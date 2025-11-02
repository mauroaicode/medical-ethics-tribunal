<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\Session\Controllers\SessionController;
use Src\Domain\Session\Models\Session;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    // Create roles
    $this->superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
    $this->adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
    $this->secretaryRole = Role::firstOrCreate(['name' => UserRole::SECRETARY->value, 'guard_name' => 'web']);

    // Create users with different roles
    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    $this->secretary = User::factory()->create();
    $this->secretary->assignRole($this->secretaryRole);

    // Create test sessions
    $this->session1 = Session::factory()->create([
        'user_id' => $this->superAdmin->id,
        'last_activity' => now()->subMinutes(30)->timestamp,
    ]);

    $this->session2 = Session::factory()->create([
        'user_id' => $this->admin->id,
        'last_activity' => now()->subMinutes(60)->timestamp,
    ]);

    $this->session3 = Session::factory()->create([
        'user_id' => $this->secretary->id,
        'last_activity' => now()->subHours(2)->timestamp,
    ]);
});

describe('index', function (): void {
    it('returns list of sessions when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([SessionController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'user',
                    'ip_address',
                    'user_agent',
                    'location',
                    'last_activity',
                ],
            ]);

        $sessions = $response->json();

        expect($sessions)->not->toBeEmpty();

        $sessionIds = collect($sessions)->pluck('id')->toArray();

        expect($sessionIds)->toContain($this->session1->id)
            ->and($sessionIds)->toContain($this->session2->id)
            ->and($sessionIds)->toContain($this->session3->id);
    });

    it('returns sessions ordered by last activity', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([SessionController::class, 'index']))
            ->assertOk();

        $sessions = $response->json();
        $activities = collect($sessions)->pluck('last_activity')->toArray();
        $sortedActivities = $activities;
        rsort($sortedActivities);

        expect($activities)->toBe($sortedActivities);
    });

    it('includes user information in sessions', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([SessionController::class, 'index']))
            ->assertOk();

        $sessions = $response->json();

        foreach ($sessions as $session) {
            if ($session['user_id'] !== null) {
                expect($session['user'])->toBeArray()
                    ->and($session['user'])->toHaveKeys(['id', 'name', 'last_name', 'email']);
            }
        }
    });

    it('fails when authenticated as admin (unauthorized)', function (): void {
        actingAs($this->admin)
            ->get(action([SessionController::class, 'index']))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->get(action([SessionController::class, 'index']))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);
    });

    it('requires authentication', function (): void {
        get(action([SessionController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns sessions in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([SessionController::class, 'index']))
            ->assertOk();

        $sessions = $response->json();

        foreach ($sessions as $session) {
            expect($session)->toHaveKeys(['id', 'user_id', 'user', 'ip_address', 'user_agent', 'location', 'last_activity'])
                ->and($session['id'])->toBeString()
                ->and($session['last_activity'])->toBeInt();

            if ($session['user_id'] !== null) {
                expect($session['user'])->toBeArray();
            } else {
                expect($session['user'])->toBeNull();
            }
        }
    });
});
