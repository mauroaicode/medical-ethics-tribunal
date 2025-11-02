<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Src\Application\Admin\User\Controllers\UserController;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

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

    // Create additional admin users for testing
    $this->adminUser1 = User::factory()->create();
    $this->adminUser1->assignRole($this->adminRole);

    $this->adminUser2 = User::factory()->create();
    $this->adminUser2->assignRole($this->secretaryRole);
});

describe('index', function (): void {
    it('returns list of users with admin roles when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([UserController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'last_name',
                    'email',
                    'document_type',
                    'document_number',
                    'phone',
                    'address',
                    'status',
                    'roles',
                ],
            ]);

        $users = $response->json();
        $userIds = collect($users)->pluck('id');

        expect($userIds)->toContain($this->superAdmin->id)
            ->and($userIds)->toContain($this->admin->id)
            ->and($userIds)->toContain($this->secretary->id)
            ->and($userIds)->toContain($this->adminUser1->id)
            ->and($userIds)->toContain($this->adminUser2->id);
    });

    it('returns list of users with admin roles when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([UserController::class, 'index']));

        $response->assertOk();

        $users = $response->json();
        $userIds = collect($users)->pluck('id');

        expect($userIds)->toContain($this->superAdmin->id)
            ->and($userIds)->toContain($this->admin->id)
            ->and($userIds)->toContain($this->secretary->id);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->get(action([UserController::class, 'index']))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);
    });

    it('returns only users with admin roles', function (): void {
        // Create a user with doctor role (non-admin)
        $doctorUser = User::factory()->create();
        $doctorRole = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $doctorUser->assignRole($doctorRole);

        $response = actingAs($this->superAdmin)
            ->get(action([UserController::class, 'index']));

        $response->assertOk();

        $users = $response->json();
        $userIds = collect($users)->pluck('id');

        expect($userIds)->not->toContain($doctorUser->id);
    });

    it('requires authentication', function (): void {
        get(action([UserController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });
});

describe('store', function (): void {
    it('creates user successfully when authenticated as super admin', function (): void {
        $data = [
            'name' => 'Nuevo',
            'last_name' => 'Usuario',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000001',
            'phone' => '3001112233',
            'address' => 'Calle Falsa 123',
            'email' => 'nuevo.usuario@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
            'status' => UserStatus::ACTIVE->value,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'last_name',
                'email',
                'document_type',
                'document_number',
                'phone',
                'address',
                'status',
                'roles',
            ]);

        expect($response->json('name'))->toBe('Nuevo')
            ->and($response->json('last_name'))->toBe('Usuario')
            ->and($response->json('email'))->toBe('nuevo.usuario@example.com');

        $createdUser = User::query()->where('email', 'nuevo.usuario@example.com')->first();
        expect($createdUser)->not->toBeNull()
            ->and($createdUser->hasRole(UserRole::ADMIN->value))->toBeTrue();
    });

    it('creates user successfully when authenticated as admin', function (): void {
        $data = [
            'name' => 'Otro',
            'last_name' => 'Usuario',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000002',
            'phone' => '3001112234',
            'address' => 'Calle Falsa 456',
            'email' => 'otro.usuario@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::SECRETARY->value],
        ];

        actingAs($this->admin)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(201);

        $createdUser = User::query()->where('email', 'otro.usuario@example.com')->first();
        expect($createdUser)->not->toBeNull()
            ->and($createdUser->hasRole(UserRole::SECRETARY->value))->toBeTrue();
    });

    it('creates user with default active status when status is not provided', function (): void {
        $data = [
            'name' => 'Usuario',
            'last_name' => 'Sin Status',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000003',
            'phone' => '3001112235',
            'address' => 'Calle Falsa 789',
            'email' => 'sin.status@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(201);

        $createdUser = User::query()->where('email', 'sin.status@example.com')->first();
        expect($createdUser->status)->toBe(UserStatus::ACTIVE);
    });

    it('creates user with multiple roles', function (): void {
        $data = [
            'name' => 'Multi',
            'last_name' => 'Rol',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000004',
            'phone' => '3001112236',
            'address' => 'Calle Falsa 101',
            'email' => 'multi.rol@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value, UserRole::SECRETARY->value],
        ];

        actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(201);

        $createdUser = User::query()->where('email', 'multi.rol@example.com')->first();
        expect($createdUser->hasRole(UserRole::ADMIN->value))->toBeTrue()
            ->and($createdUser->hasRole(UserRole::SECRETARY->value))->toBeTrue();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'name' => 'No',
            'last_name' => 'Autorizado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000005',
            'phone' => '3001112237',
            'address' => 'Calle Falsa 202',
            'email' => 'no.autorizado@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        actingAs($this->secretary)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);

        $createdUser = User::query()->where('email', 'no.autorizado@example.com')->first();
        expect($createdUser)->toBeNull();
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Sin',
            'last_name' => 'Auth',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000006',
            'phone' => '3001112238',
            'address' => 'Calle Falsa 303',
            'email' => 'sin.auth@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        post(action([UserController::class, 'store']), $data)
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'messages',
                'code',
            ]);

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), []);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });

    it('fails validation with duplicate email', function (): void {
        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Email',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000007',
            'phone' => '3001112239',
            'address' => 'Calle Falsa 404',
            'email' => $this->admin->email, // Using existing email
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with duplicate document number', function (): void {
        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Document',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $this->admin->document_number, // Using existing document number
            'phone' => '3001112240',
            'address' => 'Calle Falsa 505',
            'email' => 'duplicate.document@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with weak password', function (): void {
        $data = [
            'name' => 'Weak',
            'last_name' => 'Password',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000008',
            'phone' => '3001112241',
            'address' => 'Calle Falsa 606',
            'email' => 'weak.password@example.com',
            'password' => 'short', // Weak password
            'roles' => [UserRole::ADMIN->value],
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with invalid role', function (): void {
        $data = [
            'name' => 'Invalid',
            'last_name' => 'Role',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000009',
            'phone' => '3001112242',
            'address' => 'Calle Falsa 707',
            'email' => 'invalid.role@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => ['invalid_role'], // Invalid role
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with empty roles array', function (): void {
        $data = [
            'name' => 'Empty',
            'last_name' => 'Roles',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000010',
            'phone' => '3001112243',
            'address' => 'Calle Falsa 808',
            'email' => 'empty.roles@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [], // Empty roles
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });
});

describe('update', function (): void {
    it('updates user successfully when authenticated as super admin', function (): void {
        $data = [
            'name' => 'Actualizado',
            'last_name' => 'Usuario',
            'document_type' => DocumentType::CEDULA_EXTRANJERIA->value,
            'phone' => '3009998877',
            'address' => 'Nueva Dirección 456',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'last_name',
                'email',
                'document_type',
                'document_number',
                'phone',
                'address',
                'status',
                'roles',
            ]);

        expect($response->json('name'))->toBe('Actualizado')
            ->and($response->json('last_name'))->toBe('Usuario')
            ->and($response->json('phone'))->toBe('3009998877')
            ->and($response->json('address'))->toBe('Nueva Dirección 456');

        $this->admin->refresh();
        expect($this->admin->name)->toBe('Actualizado')
            ->and($this->admin->document_type)->toBe(DocumentType::CEDULA_EXTRANJERIA);
    });

    it('updates user successfully when authenticated as admin', function (): void {
        $data = [
            'name' => 'Otro',
            'last_name' => 'Actualizado',
        ];

        actingAs($this->admin)
            ->put(action([UserController::class, 'update'], $this->secretary->id), $data)
            ->assertOk();

        $this->secretary->refresh();
        expect($this->secretary->name)->toBe('Otro')
            ->and($this->secretary->last_name)->toBe('Actualizado');
    });

    it('updates user roles successfully', function (): void {
        $data = [
            'roles' => [UserRole::ADMIN->value, UserRole::SECRETARY->value],
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->secretary->id), $data)
            ->assertOk();

        $this->secretary->refresh();
        expect($this->secretary->hasRole(UserRole::ADMIN->value))->toBeTrue()
            ->and($this->secretary->hasRole(UserRole::SECRETARY->value))->toBeTrue();
    });

    it('updates user password successfully', function (): void {
        $newPassword = 'NewStrongPassword123!@#';
        $data = [
            'password' => $newPassword,
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk();

        $this->admin->refresh();
        expect(Hash::check($newPassword, $this->admin->password))->toBeTrue();
    });

    it('updates user status successfully', function (): void {
        $data = [
            'status' => UserStatus::INACTIVE->value,
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk();

        $this->admin->refresh();
        expect($this->admin->status)->toBe(UserStatus::INACTIVE);
    });

    it('updates only provided fields without affecting others', function (): void {
        $originalEmail = $this->admin->email;
        $originalDocumentNumber = $this->admin->document_number;

        $data = [
            'name' => 'Solo Nombre',
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk();

        $this->admin->refresh();
        expect($this->admin->name)->toBe('Solo Nombre')
            ->and($this->admin->email)->toBe($originalEmail)
            ->and($this->admin->document_number)->toBe($originalDocumentNumber);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'name' => 'No',
            'last_name' => 'Autorizado',
        ];

        actingAs($this->secretary)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);

        $this->admin->refresh();
        expect($this->admin->name)->not->toBe('No');
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Sin',
            'last_name' => 'Auth',
        ];

        put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('fails validation with duplicate email', function (): void {
        $data = [
            'email' => $this->superAdmin->email, // Using existing email
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with duplicate document number', function (): void {
        $data = [
            'document_number' => $this->superAdmin->document_number, // Using existing document number
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('allows updating with same email and document number for same user', function (): void {
        $data = [
            'name' => 'Mismo Usuario',
            'email' => $this->admin->email, // Same email
            'document_number' => $this->admin->document_number, // Same document number
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk();

        $this->admin->refresh();
        expect($this->admin->name)->toBe('Mismo Usuario');
    });

    it('fails validation with weak password', function (): void {
        $data = [
            'password' => 'short',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with invalid role', function (): void {
        $data = [
            'roles' => ['invalid_role'],
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });

    it('fails validation with empty roles array', function (): void {
        $data = [
            'roles' => [],
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray();
    });
});

describe('destroy', function (): void {
    it('deletes user successfully when authenticated as super admin', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([UserController::class, 'destroy'], $this->admin->id))
            ->assertStatus(204);

        $this->admin->refresh();

        expect($this->admin->trashed())->toBeTrue()
            ->and($this->admin->status)->toBe(UserStatus::INACTIVE);
    });

    it('deletes user successfully when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->delete(action([UserController::class, 'destroy'], $this->secretary->id))
            ->assertStatus(204);

        $this->secretary->refresh();

        expect($this->secretary->trashed())->toBeTrue()
            ->and($this->secretary->status)->toBe(UserStatus::INACTIVE);
    });

    it('creates audit log entry when deleting user', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([UserController::class, 'destroy'], $this->admin->id))
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', User::class)
            ->where('auditable_id', $this->admin->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeNull()
            ->and($auditLog->ip_address)->not->toBeNull()
            ->and($auditLog->user_agent)->not->toBeNull()
            ->and($auditLog->location)->not->toBeNull();
    });

    it('excludes deleted user from list', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([UserController::class, 'destroy'], $this->admin->id))
            ->assertStatus(204);

        $response = actingAs($this->superAdmin)
            ->get(action([UserController::class, 'index']))
            ->assertOk();

        $users = $response->json();
        $userIds = collect($users)->pluck('id');

        expect($userIds)->not->toContain($this->admin->id);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $originalStatus = $this->admin->status;

        actingAs($this->secretary)
            ->delete(action([UserController::class, 'destroy'], $this->admin->id))
            ->assertStatus(403)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 403,
            ]);

        $this->admin->refresh();

        expect($this->admin->trashed())->toBeFalse()
            ->and($this->admin->status)->toBe($originalStatus);
    });

    it('requires authentication', function (): void {
        $originalStatus = $this->admin->status;

        delete(action([UserController::class, 'destroy'], $this->admin->id))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);

        $this->admin->refresh();

        expect($this->admin->trashed())->toBeFalse()
            ->and($this->admin->status)->toBe($originalStatus);
    });

    it('creates audit log entry when creating user', function (): void {
        $data = [
            'name' => 'Test',
            'last_name' => 'Audit',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '9999999999',
            'phone' => '3009999999',
            'address' => 'Test Address',
            'email' => 'test.audit@example.com',
            'password' => 'StrongPassword123!@#',
            'roles' => [UserRole::ADMIN->value],
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([UserController::class, 'store']), $data)
            ->assertStatus(201);

        $userId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', User::class)
            ->where('auditable_id', $userId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray()
            ->and($auditLog->ip_address)->not->toBeNull()
            ->and($auditLog->user_agent)->not->toBeNull()
            ->and($auditLog->location)->not->toBeNull();
    });

    it('creates audit log entry when updating user', function (): void {
        $data = [
            'name' => 'Updated Name',
        ];

        actingAs($this->superAdmin)
            ->put(action([UserController::class, 'update'], $this->admin->id), $data)
            ->assertOk();

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', User::class)
            ->where('auditable_id', $this->admin->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeArray()
            ->and($auditLog->ip_address)->not->toBeNull()
            ->and($auditLog->user_agent)->not->toBeNull()
            ->and($auditLog->location)->not->toBeNull();
    });
});
