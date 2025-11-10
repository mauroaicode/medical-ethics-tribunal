<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\Complainant\Controllers\ComplainantController;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\City\Models\City;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserRole;
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

    // Get or create a city for testing
    $this->city = City::query()->first() ?? City::query()->create([
        'codigo' => '19001',
        'iddepartamento' => 1,
        'descripcion' => 'POPAYÁN',
    ]);

    // Create complainants for testing
    $this->complainant1 = Complainant::factory()->create(['city_id' => $this->city->id]);
    $this->complainant2 = Complainant::factory()->create(['city_id' => $this->city->id]);
});

describe('index', function (): void {
    it('returns list of complainants when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ComplainantController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'name',
                    'last_name',
                    'email',
                    'location',
                    'is_anonymous',
                    'created_at',
                ],
            ]);

        $complainants = $response->json();
        $complainantIds = collect($complainants)->pluck('id');

        expect($complainantIds)->toContain($this->complainant1->id)
            ->and($complainantIds)->toContain($this->complainant2->id);
    });

    it('returns list of complainants when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([ComplainantController::class, 'index']));

        $response->assertOk();

        $complainants = $response->json();
        $complainantIds = collect($complainants)->pluck('id');

        expect($complainantIds)->toContain($this->complainant1->id)
            ->and($complainantIds)->toContain($this->complainant2->id);
    });

    it('returns list of complainants when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([ComplainantController::class, 'index']))
            ->assertOk();

        $complainants = $response->json();
        $complainantIds = collect($complainants)->pluck('id');

        expect($complainantIds)->toContain($this->complainant1->id)
            ->and($complainantIds)->toContain($this->complainant2->id);
    });

    it('requires authentication', function (): void {
        get(action([ComplainantController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });
});

describe('show', function (): void {
    it('returns complainant details when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ComplainantController::class, 'show'], $this->complainant1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'city_id',
                'municipality',
                'company',
                'is_anonymous',
                'user',
                'city',
            ]);

        expect($response->json('id'))->toBe($this->complainant1->id);
    });

    it('returns complainant details when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->get(action([ComplainantController::class, 'show'], $this->complainant1->id))
            ->assertOk();
    });

    it('returns complainant details when authenticated as secretary', function (): void {
        actingAs($this->secretary)
            ->get(action([ComplainantController::class, 'show'], $this->complainant1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'city_id',
                'municipality',
                'company',
                'is_anonymous',
                'user',
                'city',
            ]);
    });

    it('requires authentication', function (): void {
        get(action([ComplainantController::class, 'show'], $this->complainant1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when complainant not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ComplainantController::class, 'show'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });
});

describe('store', function (): void {
    it('creates complainant successfully when authenticated as super admin', function (): void {
        $uniqueId = time() + 600;
        $email = "nuevo.quejoso.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Nuevo',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112233',
            'address' => 'Calle Falsa 123',
            'email' => $email,
            'city_id' => $this->city->id,
            'municipality' => 'Test Municipality',
            'company' => 'Test Company',
            'is_anonymous' => false,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'city_id',
                'municipality',
                'company',
                'is_anonymous',
                'user',
                'city',
            ]);

        expect($response->json('user.email'))->toBe($email);

        $createdComplainant = Complainant::query()->where('user_id', $response->json('user.id'))->first();
        expect($createdComplainant)->not->toBeNull();
    });

    it('creates complainant successfully when authenticated as admin', function (): void {
        $uniqueId = time() + 601;
        $email = "otro.quejoso.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Otro',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112234',
            'address' => 'Calle Falsa 456',
            'email' => $email,
            'city_id' => $this->city->id,
        ];

        actingAs($this->admin)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(201);

        $createdComplainant = Complainant::query()->whereHas('user', fn ($q) => $q->where('email', $email))->first();
        expect($createdComplainant)->not->toBeNull();
    });

    it('creates complainant with anonymous flag', function (): void {
        $uniqueId = time() + 602;
        $email = "anonimo.quejoso.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Anónimo',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112235',
            'address' => 'Calle Falsa 789',
            'email' => $email,
            'city_id' => $this->city->id,
            'is_anonymous' => true,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(201);

        expect($response->json('is_anonymous'))->toBeTrue();
    });

    it('creates audit log entry when creating complainant', function (): void {
        $uniqueId = time() + 603;
        $email = "test.quejoso.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Test',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3009999999',
            'address' => 'Test Address',
            'email' => $email,
            'city_id' => $this->city->id,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(201);

        $complainantId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', Complainant::class)
            ->where('auditable_id', $complainantId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $uniqueId = time() + 604;
        $data = [
            'name' => 'No',
            'last_name' => 'Autorizado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112235',
            'address' => 'Calle Falsa 789',
            'email' => "no.autorizado.{$uniqueId}@example.com",
            'city_id' => $this->city->id,
        ];

        actingAs($this->secretary)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $uniqueId = time() + 605;
        $data = [
            'name' => 'Test',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112236',
            'address' => 'Test Address',
            'email' => "test.{$uniqueId}@example.com",
            'city_id' => $this->city->id,
        ];

        post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(401);
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([ComplainantController::class, 'store']), [])
            ->assertStatus(422);
    });

    it('fails validation with invalid city_id', function (): void {
        $uniqueId = time() + 606;
        $data = [
            'name' => 'Test',
            'last_name' => 'Quejoso',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112237',
            'address' => 'Test Address',
            'email' => "test2.{$uniqueId}@example.com",
            'city_id' => 99999,
        ];

        actingAs($this->superAdmin)
            ->post(action([ComplainantController::class, 'store']), $data)
            ->assertStatus(422);
    });
});

describe('update', function (): void {
    it('updates complainant successfully when authenticated as super admin', function (): void {
        $data = [
            'name' => 'Actualizado',
            'last_name' => 'Quejoso',
            'municipality' => 'Nuevo Municipio',
        ];

        actingAs($this->superAdmin)
            ->put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(200);

        $this->complainant1->refresh();
        $this->complainant1->load('user');

        expect($this->complainant1->user->name)->toBe('Actualizado')
            ->and($this->complainant1->municipality)->toBe('Nuevo Municipio');
    });

    it('updates complainant successfully when authenticated as admin', function (): void {
        $data = [
            'company' => 'Nueva Empresa',
        ];

        actingAs($this->admin)
            ->put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(200);

        $this->complainant1->refresh();

        expect($this->complainant1->company)->toBe('Nueva Empresa');
    });

    it('updates complainant with anonymous flag', function (): void {
        $data = [
            'is_anonymous' => true,
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(200);

        expect($response->json('is_anonymous'))->toBeTrue();
    });

    it('creates audit log entry when updating complainant', function (): void {
        $oldValues = $this->complainant1->getAttributes();

        $data = [
            'municipality' => 'Municipio Actualizado',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(200);

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', Complainant::class)
            ->where('auditable_id', $this->complainant1->id)
            ->latest()
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'name' => 'No Actualizado',
        ];

        actingAs($this->secretary)
            ->put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Test',
        ];

        put(action([ComplainantController::class, 'update'], $this->complainant1->id), $data)
            ->assertStatus(401);
    });

    it('returns 404 without JSON when complainant not found', function (): void {
        $data = [
            'name' => 'Test',
        ];

        actingAs($this->superAdmin)
            ->put(action([ComplainantController::class, 'update'], 99999), $data)
            ->assertStatus(404)
            ->assertContent('');
    });
});

describe('destroy', function (): void {
    it('deletes complainant successfully when authenticated as super admin', function (): void {
        $complainantToDelete = Complainant::factory()->create(['city_id' => $this->city->id]);

        actingAs($this->superAdmin)
            ->delete(action([ComplainantController::class, 'destroy'], $complainantToDelete->id))
            ->assertStatus(204);

        expect(Complainant::query()->find($complainantToDelete->id))->toBeNull()
            ->and(Complainant::withTrashed()->find($complainantToDelete->id))->not->toBeNull();
    });

    it('deletes complainant successfully when authenticated as admin', function (): void {
        $complainantToDelete = Complainant::factory()->create(['city_id' => $this->city->id]);

        actingAs($this->admin)
            ->delete(action([ComplainantController::class, 'destroy'], $complainantToDelete->id))
            ->assertStatus(204);
    });

    it('deactivates associated user when deleting complainant', function (): void {
        $complainantToDelete = Complainant::factory()->create(['city_id' => $this->city->id]);
        $userId = $complainantToDelete->user_id;

        actingAs($this->superAdmin)
            ->delete(action([ComplainantController::class, 'destroy'], $complainantToDelete->id))
            ->assertStatus(204);

        $user = User::query()->find($userId);
        expect($user->status->value)->toBe('inactive');
    });

    it('creates audit log entry when deleting complainant', function (): void {
        $complainantToDelete = Complainant::factory()->create(['city_id' => $this->city->id]);
        $oldValues = $complainantToDelete->getAttributes();

        actingAs($this->superAdmin)
            ->delete(action([ComplainantController::class, 'destroy'], $complainantToDelete->id))
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', Complainant::class)
            ->where('auditable_id', $complainantToDelete->id)
            ->latest()
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->delete(action([ComplainantController::class, 'destroy'], $this->complainant1->id))
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        delete(action([ComplainantController::class, 'destroy'], $this->complainant1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when complainant not found', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([ComplainantController::class, 'destroy'], 99999))
            ->assertStatus(404)
            ->assertContent('');
    });
});
