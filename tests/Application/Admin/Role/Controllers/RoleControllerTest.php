<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\Role\Controllers\RoleController;
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
});

describe('index', function (): void {
    it('returns list of available roles when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([RoleController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'value',
                    'label',
                ],
            ]);

        $roles = $response->json();

        expect($roles)->toHaveCount(3)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::SUPER_ADMIN->value)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::ADMIN->value)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::SECRETARY->value);

        // Verify labels are translated
        $superAdminRole = collect($roles)->firstWhere('value', UserRole::SUPER_ADMIN->value);
        expect($superAdminRole['label'])->toBe(__('enums.user_role.super_admin'));

        $adminRole = collect($roles)->firstWhere('value', UserRole::ADMIN->value);
        expect($adminRole['label'])->toBe(__('enums.user_role.admin'));

        $secretaryRole = collect($roles)->firstWhere('value', UserRole::SECRETARY->value);
        expect($secretaryRole['label'])->toBe(__('enums.user_role.secretary'));
    });

    it('returns list of available roles when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([RoleController::class, 'index']))
            ->assertOk();

        $roles = $response->json();

        expect($roles)->toHaveCount(3)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::SUPER_ADMIN->value)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::ADMIN->value)
            ->and(collect($roles)->pluck('value'))->toContain(UserRole::SECRETARY->value);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->get(action([RoleController::class, 'index']))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);
    });

    it('requires authentication', function (): void {
        get(action([RoleController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns roles in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([RoleController::class, 'index']))
            ->assertOk();

        $roles = $response->json();

        foreach ($roles as $role) {
            expect($role)->toHaveKeys(['value', 'label'])
                ->and($role['value'])->toBeString()
                ->and($role['label'])->toBeString()
                ->and($role['value'])->not->toBeEmpty()
                ->and($role['label'])->not->toBeEmpty();
        }
    });

    it('returns all roles from UserRole enum', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([RoleController::class, 'index']))
            ->assertOk();

        $roles = $response->json();
        $roleValues = collect($roles)->pluck('value')->toArray();

        expect($roleValues)->toBe(UserRole::values());
    });
});
