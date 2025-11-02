<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\Magistrate\Controllers\MagistrateController;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Magistrate\Models\Magistrate;
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

    // Create magistrates for testing
    $this->magistrate1 = Magistrate::factory()->create();
    $this->magistrate2 = Magistrate::factory()->create();
});

describe('index', function (): void {
    it('returns list of magistrates when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MagistrateController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'user',
                ],
            ]);

        $magistrates = $response->json();
        $magistrateIds = collect($magistrates)->pluck('id');

        expect($magistrateIds)->toContain($this->magistrate1->id)
            ->and($magistrateIds)->toContain($this->magistrate2->id);
    });

    it('returns list of magistrates when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([MagistrateController::class, 'index']));

        $response->assertOk();

        $magistrates = $response->json();
        $magistrateIds = collect($magistrates)->pluck('id');

        expect($magistrateIds)->toContain($this->magistrate1->id)
            ->and($magistrateIds)->toContain($this->magistrate2->id);
    });

    it('returns list of magistrates when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([MagistrateController::class, 'index']))
            ->assertOk();

        $magistrates = $response->json();
        $magistrateIds = collect($magistrates)->pluck('id');

        expect($magistrateIds)->toContain($this->magistrate1->id)
            ->and($magistrateIds)->toContain($this->magistrate2->id);
    });

    it('requires authentication', function (): void {
        get(action([MagistrateController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });
});

describe('show', function (): void {
    it('returns magistrate details when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MagistrateController::class, 'show'], $this->magistrate1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
            ]);

        expect($response->json('id'))->toBe($this->magistrate1->id);
    });

    it('returns magistrate details when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->get(action([MagistrateController::class, 'show'], $this->magistrate1->id))
            ->assertOk();
    });

    it('returns magistrate details when authenticated as secretary', function (): void {
        actingAs($this->secretary)
            ->get(action([MagistrateController::class, 'show'], $this->magistrate1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
            ]);
    });

    it('requires authentication', function (): void {
        get(action([MagistrateController::class, 'show'], $this->magistrate1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when magistrate not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MagistrateController::class, 'show'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });
});

describe('store', function (): void {
    it('creates magistrate successfully when authenticated as super admin', function (): void {
        $uniqueId = time();
        $email = "nuevo.magistrado.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Nuevo',
            'last_name' => 'Magistrado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112233',
            'address' => 'Calle Falsa 123',
            'email' => $email,
            'password' => 'StrongPassword123!@#',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
            ]);

        expect($response->json('user.email'))->toBe($email);

        $createdMagistrate = Magistrate::query()->where('user_id', $response->json('user.id'))->first();
        expect($createdMagistrate)->not->toBeNull();
    });

    it('creates magistrate successfully when authenticated as admin', function (): void {
        $uniqueId = time() + 1;
        $email = "otro.magistrado.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Otro',
            'last_name' => 'Magistrado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112234',
            'address' => 'Calle Falsa 456',
            'email' => $email,
            'password' => 'StrongPassword123!@#',
        ];

        actingAs($this->admin)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(201);

        $createdMagistrate = Magistrate::query()->whereHas('user', fn ($q) => $q->where('email', $email))->first();
        expect($createdMagistrate)->not->toBeNull();
    });

    it('creates audit log entry when creating magistrate', function (): void {
        $uniqueId = time() + 2;
        $email = "test.magistrado.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Test',
            'last_name' => 'Magistrado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3009999999',
            'address' => 'Test Address',
            'email' => $email,
            'password' => 'StrongPassword123!@#',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(201);

        $magistrateId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', Magistrate::class)
            ->where('auditable_id', $magistrateId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $uniqueId = time() + 3;
        $data = [
            'name' => 'No',
            'last_name' => 'Autorizado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112235',
            'address' => 'Calle Falsa 789',
            'email' => "no.autorizado.{$uniqueId}@example.com",
            'password' => 'StrongPassword123!@#',
        ];

        actingAs($this->secretary)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $uniqueId = time() + 4;
        $data = [
            'name' => 'Sin',
            'last_name' => 'Auth',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112236',
            'address' => 'Calle Falsa 101',
            'email' => "sin.auth.{$uniqueId}@example.com",
            'password' => 'StrongPassword123!@#',
        ];

        post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(401);
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), [])
            ->assertStatus(422);
    });

    it('fails validation with duplicate email', function (): void {
        $existingUser = User::factory()->create();

        $uniqueId = time() + 5;
        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Email',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112237',
            'address' => 'Calle Falsa 202',
            'email' => $existingUser->email,
            'password' => 'StrongPassword123!@#',
        ];

        actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with duplicate document number', function (): void {
        $existingUser = User::factory()->create();

        $uniqueId = time() + 6;
        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Document',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $existingUser->document_number,
            'phone' => '3001112238',
            'address' => 'Calle Falsa 303',
            'email' => "duplicate.document.{$uniqueId}@example.com",
            'password' => 'StrongPassword123!@#',
        ];

        actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with weak password', function (): void {
        $uniqueId = time() + 7;
        $data = [
            'name' => 'Weak',
            'last_name' => 'Password',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112239',
            'address' => 'Calle Falsa 404',
            'email' => "weak.password.{$uniqueId}@example.com",
            'password' => 'weak',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([MagistrateController::class, 'store']), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });
});

describe('update', function (): void {
    it('updates magistrate successfully when authenticated as super admin', function (): void {
        $data = [
            'name' => 'Actualizado',
            'last_name' => 'Magistrado',
        ];

        actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(200);

        $this->magistrate1->refresh();
        expect($this->magistrate1->user->name)->toBe('Actualizado')
            ->and($this->magistrate1->user->last_name)->toBe('Magistrado');
    });

    it('updates magistrate successfully when authenticated as admin', function (): void {
        $data = [
            'phone' => '3009999999',
            'address' => 'Nueva Dirección',
        ];

        actingAs($this->admin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate2->id), $data)
            ->assertStatus(200);

        $this->magistrate2->refresh();
        expect($this->magistrate2->user->phone)->toBe('3009999999')
            ->and($this->magistrate2->user->address)->toBe('Nueva Dirección');
    });

    it('creates audit log entry when updating magistrate', function (): void {
        $oldName = $this->magistrate1->user->name;
        $data = [
            'name' => 'Nuevo Nombre',
        ];

        actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(200);

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', Magistrate::class)
            ->where('auditable_id', $this->magistrate1->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'name' => 'No Autorizado',
        ];

        actingAs($this->secretary)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Sin Auth',
        ];

        put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(401);
    });

    it('fails validation with duplicate email', function (): void {
        $otherUser = User::factory()->create();

        $data = [
            'email' => $otherUser->email,
        ];

        actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(422);
    });

    it('fails validation with duplicate document number', function (): void {
        $otherUser = User::factory()->create();

        $data = [
            'document_number' => $otherUser->document_number,
        ];

        actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data)
            ->assertStatus(422);
    });

    it('fails validation with weak password', function (): void {
        $data = [
            'password' => 'weak',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], $this->magistrate1->id), $data);

        $response->assertStatus(422);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->not->toBeEmpty();
    });
});

describe('destroy', function (): void {
    it('deletes magistrate successfully when authenticated as super admin', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([MagistrateController::class, 'destroy'], $this->magistrate1->id))
            ->assertStatus(204);

        $this->magistrate1->refresh();
        expect($this->magistrate1->trashed())->toBeTrue()
            ->and($this->magistrate1->user->status)->toBe(UserStatus::INACTIVE);
    });

    it('deletes magistrate successfully when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->delete(action([MagistrateController::class, 'destroy'], $this->magistrate2->id))
            ->assertStatus(204);

        $this->magistrate2->refresh();
        expect($this->magistrate2->trashed())->toBeTrue()
            ->and($this->magistrate2->user->status)->toBe(UserStatus::INACTIVE);
    });

    it('creates audit log entry when deleting magistrate', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([MagistrateController::class, 'destroy'], $this->magistrate1->id))
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', Magistrate::class)
            ->where('auditable_id', $this->magistrate1->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeNull();
    });

    it('excludes deleted magistrate from list', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([MagistrateController::class, 'destroy'], $this->magistrate1->id))
            ->assertStatus(204);

        $response = actingAs($this->superAdmin)
            ->get(action([MagistrateController::class, 'index']))
            ->assertOk();

        $magistrates = $response->json();
        $magistrateIds = collect($magistrates)->pluck('id');

        expect($magistrateIds)->not->toContain($this->magistrate1->id);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->delete(action([MagistrateController::class, 'destroy'], $this->magistrate1->id))
            ->assertStatus(403);

        $this->magistrate1->refresh();
        expect($this->magistrate1->trashed())->toBeFalse();
    });

    it('requires authentication', function (): void {
        delete(action([MagistrateController::class, 'destroy'], $this->magistrate1->id))
            ->assertStatus(401);

        $this->magistrate1->refresh();
        expect($this->magistrate1->trashed())->toBeFalse();
    });

    it('returns 404 without JSON when magistrate not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->delete(action([MagistrateController::class, 'destroy'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });

    it('returns 404 without JSON when magistrate not found for update', function (): void {
        $data = [
            'name' => 'Not Found',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([MagistrateController::class, 'update'], 99999), $data);

        $response->assertStatus(404)
            ->assertContent('');
    });
});
