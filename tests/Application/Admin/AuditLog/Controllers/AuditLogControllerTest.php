<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\AuditLog\Controllers\AuditLogController;
use Src\Domain\AuditLog\Models\AuditLog;
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

    // Create test audit logs
    $this->auditLog1 = AuditLog::create([
        'user_id' => $this->superAdmin->id,
        'action' => 'create',
        'auditable_type' => User::class,
        'auditable_id' => $this->admin->id,
        'old_values' => null,
        'new_values' => ['name' => 'Test User'],
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Test Agent',
        'location' => 'Bogotá, Cundinamarca, Colombia',
        'created_at' => now()->subMinutes(30),
    ]);

    $this->auditLog2 = AuditLog::create([
        'user_id' => $this->admin->id,
        'action' => 'update',
        'auditable_type' => User::class,
        'auditable_id' => $this->secretary->id,
        'old_values' => ['name' => 'Old Name'],
        'new_values' => ['name' => 'New Name'],
        'ip_address' => '192.168.1.2',
        'user_agent' => 'Test Agent 2',
        'location' => 'Medellín, Antioquia, Colombia',
        'created_at' => now()->subMinutes(60),
    ]);

    $this->auditLog3 = AuditLog::create([
        'user_id' => $this->superAdmin->id,
        'action' => 'delete',
        'auditable_type' => User::class,
        'auditable_id' => $this->admin->id,
        'old_values' => ['name' => 'Deleted User'],
        'new_values' => null,
        'ip_address' => '192.168.1.3',
        'user_agent' => 'Test Agent 3',
        'location' => null,
        'created_at' => now()->subHours(2),
    ]);
});

describe('index', function (): void {
    it('returns list of audit logs when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'user',
                    'action',
                    'auditable_type',
                    'auditable_id',
                    'auditable',
                    'old_values',
                    'new_values',
                    'ip_address',
                    'user_agent',
                    'location',
                    'created_at',
                ],
            ]);

        $auditLogs = $response->json();

        expect($auditLogs)->not->toBeEmpty();

        $auditLogIds = collect($auditLogs)->pluck('id')->toArray();

        expect($auditLogIds)->toContain($this->auditLog1->id)
            ->and($auditLogIds)->toContain($this->auditLog2->id)
            ->and($auditLogIds)->toContain($this->auditLog3->id);
    });

    it('returns list of audit logs when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();

        expect($auditLogs)->not->toBeEmpty();

        $auditLogIds = collect($auditLogs)->pluck('id')->toArray();

        expect($auditLogIds)->toContain($this->auditLog1->id)
            ->and($auditLogIds)->toContain($this->auditLog2->id)
            ->and($auditLogIds)->toContain($this->auditLog3->id);
    });

    it('returns audit logs ordered by created_at', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();
        $createdAts = collect($auditLogs)->pluck('created_at')->toArray();

        // Verify they are ordered from newest to oldest (descending)
        $sortedCreatedAts = $createdAts;
        rsort($sortedCreatedAts);

        expect($createdAts)->toBe($sortedCreatedAts);
    });

    it('includes user information in audit logs', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();

        foreach ($auditLogs as $auditLog) {
            expect($auditLog['user'])->toBeArray()
                ->and($auditLog['user'])->toHaveKeys(['id', 'name', 'last_name', 'email']);
        }
    });

    it('includes auditable information in audit logs', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();

        foreach ($auditLogs as $auditLog) {
            if ($auditLog['auditable'] !== null) {
                expect($auditLog['auditable'])->toBeArray()
                    ->and($auditLog['auditable'])->toHaveKeys(['id', 'type']);
            }
        }
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->get(action([AuditLogController::class, 'index']))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);
    });

    it('requires authentication', function (): void {
        get(action([AuditLogController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns audit logs in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();

        foreach ($auditLogs as $auditLog) {
            expect($auditLog)->toHaveKeys([
                'id',
                'user_id',
                'user',
                'action',
                'auditable_type',
                'auditable_id',
                'auditable',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
                'location',
                'created_at',
            ])
                ->and($auditLog['id'])->toBeInt()
                ->and($auditLog['user_id'])->toBeInt()
                ->and($auditLog['action'])->toBeString()
                ->and($auditLog['auditable_type'])->toBeString()
                ->and($auditLog['auditable_id'])->toBeInt()
                ->and($auditLog['ip_address'])->toBeString()
                ->and($auditLog['created_at'])->toBeString();
        }
    });

    it('handles null values correctly', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([AuditLogController::class, 'index']))
            ->assertOk();

        $auditLogs = $response->json();

        // Find the delete action audit log
        $deleteLog = collect($auditLogs)->firstWhere('action', 'delete');

        expect($deleteLog)->not->toBeNull()
            ->and($deleteLog['old_values'])->toBeArray()
            ->and($deleteLog['new_values'])->toBeNull();
    });
});
