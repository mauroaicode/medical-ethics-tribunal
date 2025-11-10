<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Src\Application\Admin\Doctor\Controllers\DoctorController;
use Src\Application\Shared\Notifications\AccountCreatedNotification;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;
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
    Notification::fake();

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

    // Create medical specialty (use first or create to avoid duplicates)
    $this->specialty = MedicalSpecialty::query()->first() ?? MedicalSpecialty::factory()->create();

    // Create doctors for testing
    $this->doctor1 = Doctor::factory()->create(['specialty_id' => $this->specialty->id]);
    $this->doctor2 = Doctor::factory()->create(['specialty_id' => $this->specialty->id]);
});

describe('index', function (): void {
    it('returns list of doctors when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DoctorController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'user_id',
                    'name',
                    'last_name',
                    'email',
                    'specialty',
                    'phone',
                    'main_practice_company',
                    'created_at',
                ],
            ]);

        $doctors = $response->json();
        $doctorIds = collect($doctors)->pluck('id');

        expect($doctorIds)->toContain($this->doctor1->id)
            ->and($doctorIds)->toContain($this->doctor2->id);
    });

    it('returns list of doctors when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([DoctorController::class, 'index']));

        $response->assertOk();

        $doctors = $response->json();
        $doctorIds = collect($doctors)->pluck('id');

        expect($doctorIds)->toContain($this->doctor1->id)
            ->and($doctorIds)->toContain($this->doctor2->id);
    });

    it('returns list of doctors when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([DoctorController::class, 'index']))
            ->assertOk();

        $doctors = $response->json();
        $doctorIds = collect($doctors)->pluck('id');

        expect($doctorIds)->toContain($this->doctor1->id)
            ->and($doctorIds)->toContain($this->doctor2->id);
    });

    it('requires authentication', function (): void {
        get(action([DoctorController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });
});

describe('show', function (): void {
    it('returns doctor details when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DoctorController::class, 'show'], $this->doctor1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
                'specialty_id',
                'specialty',
                'faculty',
                'medical_registration_number',
                'medical_registration_place',
                'medical_registration_date',
            ]);

        expect($response->json('id'))->toBe($this->doctor1->id);
    });

    it('returns doctor details when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->get(action([DoctorController::class, 'show'], $this->doctor1->id))
            ->assertOk();
    });

    it('returns doctor details when authenticated as secretary', function (): void {
        actingAs($this->secretary)
            ->get(action([DoctorController::class, 'show'], $this->doctor1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
                'specialty_id',
                'specialty',
                'faculty',
                'medical_registration_number',
                'medical_registration_place',
                'medical_registration_date',
            ]);
    });

    it('requires authentication', function (): void {
        get(action([DoctorController::class, 'show'], $this->doctor1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when doctor not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([DoctorController::class, 'show'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });
});

describe('store', function (): void {
    it('creates doctor successfully when authenticated as super admin', function (): void {
        $uniqueId = time();
        $data = [
            'name' => 'Juan',
            'last_name' => 'Pérez',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112233',
            'address' => 'Calle Falsa 123',
            'email' => "juan.perez.{$uniqueId}@example.com",
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Nacional',
            'medical_registration_number' => "M{$uniqueId}",
            'medical_registration_place' => 'Bogotá',
            'medical_registration_date' => '2010-01-15',
            'main_practice_company' => 'Hospital Central',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'user',
                'specialty_id',
                'specialty',
                'faculty',
                'medical_registration_number',
            ]);

        expect($response->json('faculty'))->toBe('Universidad Nacional')
            ->and($response->json('medical_registration_number'))->toBe("M{$uniqueId}");

        $createdDoctor = Doctor::query()->where('medical_registration_number', "M{$uniqueId}")->first();
        expect($createdDoctor)->not->toBeNull()
            ->and($createdDoctor->user->email)->toBe("juan.perez.{$uniqueId}@example.com");
    });

    it('creates doctor successfully when authenticated as admin', function (): void {
        $uniqueId = time() + 1;
        $data = [
            'name' => 'María',
            'last_name' => 'González',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => "100000{$uniqueId}",
            'phone' => '3001112234',
            'address' => 'Calle Falsa 456',
            'email' => "maria.gonzalez.{$uniqueId}@example.com",
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad de Antioquia',
            'medical_registration_number' => "M{$uniqueId}",
            'medical_registration_place' => 'Medellín',
            'medical_registration_date' => '2012-05-20',
        ];

        actingAs($this->admin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(201);

        $createdDoctor = Doctor::query()->where('medical_registration_number', "M{$uniqueId}")->first();
        expect($createdDoctor)->not->toBeNull();
    });

    it('creates audit log entry when creating doctor', function (): void {
        $uniqueId = time() + 500;
        $documentNumber = "100000{$uniqueId}";
        $email = "test.doctor.{$uniqueId}@example.com";
        $medicalRegistrationNumber = "M{$uniqueId}";

        $data = [
            'name' => 'Test',
            'last_name' => 'Doctor',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3009999999',
            'address' => 'Test Address',
            'email' => $email,
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Test University',
            'medical_registration_number' => $medicalRegistrationNumber,
            'medical_registration_place' => 'Test City',
            'medical_registration_date' => '2015-01-01',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(201);

        $doctorId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', Doctor::class)
            ->where('auditable_id', $doctorId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'name' => 'No',
            'last_name' => 'Autorizado',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000003',
            'phone' => '3001112235',
            'address' => 'Calle Falsa 789',
            'email' => 'no.autorizado@example.com',
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => 'M11111111',
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        actingAs($this->secretary)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(403);

        $createdDoctor = Doctor::query()->where('medical_registration_number', 'M11111111')->first();
        expect($createdDoctor)->toBeNull();
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Sin',
            'last_name' => 'Auth',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000004',
            'phone' => '3001112236',
            'address' => 'Calle Falsa 101',
            'email' => 'sin.auth@example.com',
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => 'M22222222',
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(401);
    });

    it('does not send account created notification when email notification is disabled', function (): void {
        config(['auth.doctor_magistrate.email_notification_enabled' => false]);

        $uniqueId = time() + 200;
        $email = "doctor.notification.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Notification',
            'last_name' => 'Test',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112241',
            'address' => 'Calle Falsa 606',
            'email' => $email,
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => "M{$uniqueId}",
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(201);

        $createdDoctor = Doctor::query()->where('medical_registration_number', "M{$uniqueId}")->first();
        $createdUser = $createdDoctor->user;

        Notification::assertNothingSent();

        expect($createdUser->requires_password_change)->toBeFalse();
    });

    it('sends account created notification when email notification is enabled', function (): void {
        config(['auth.doctor_magistrate.email_notification_enabled' => true]);

        $uniqueId = time() + 201;
        $email = "doctor.notification2.{$uniqueId}@example.com";
        $documentNumber = "100000{$uniqueId}";

        $data = [
            'name' => 'Notification',
            'last_name' => 'Test',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => $documentNumber,
            'phone' => '3001112242',
            'address' => 'Calle Falsa 607',
            'email' => $email,
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => "M{$uniqueId}",
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(201);

        $createdDoctor = Doctor::query()->where('medical_registration_number', "M{$uniqueId}")->first();
        $createdUser = $createdDoctor->user;

        Notification::assertSentTo($createdUser, AccountCreatedNotification::class);

        expect($createdUser->requires_password_change)->toBeTrue();
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), [])
            ->assertStatus(422);
    });

    it('fails validation with duplicate email', function (): void {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Email',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000005',
            'phone' => '3001112237',
            'address' => 'Calle Falsa 202',
            'email' => $existingUser->email,
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => 'M33333333',
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with duplicate medical registration number', function (): void {
        $data = [
            'name' => 'Duplicate',
            'last_name' => 'Registration',
            'document_type' => DocumentType::CEDULA_CIUDADANIA->value,
            'document_number' => '1000000006',
            'phone' => '3001112238',
            'address' => 'Calle Falsa 303',
            'email' => 'duplicate.registration@example.com',
            'specialty_id' => $this->specialty->id,
            'faculty' => 'Universidad Test',
            'medical_registration_number' => $this->doctor1->medical_registration_number,
            'medical_registration_place' => 'Test',
            'medical_registration_date' => '2010-01-01',
        ];

        actingAs($this->superAdmin)
            ->post(action([DoctorController::class, 'store']), $data)
            ->assertStatus(422);
    });
});

describe('update', function (): void {
    it('updates doctor successfully when authenticated as super admin', function (): void {
        $data = [
            'faculty' => 'Nueva Universidad',
            'medical_registration_place' => 'Nueva Ciudad',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([DoctorController::class, 'update'], $this->doctor1->id), $data)
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'faculty',
                'medical_registration_place',
            ]);

        expect($response->json('faculty'))->toBe('Nueva Universidad')
            ->and($response->json('medical_registration_place'))->toBe('Nueva Ciudad');

        $this->doctor1->refresh();
        expect($this->doctor1->faculty)->toBe('Nueva Universidad');
    });

    it('updates doctor user data successfully', function (): void {
        $data = [
            'name' => 'Actualizado',
            'last_name' => 'Nombre',
            'phone' => '3009998877',
        ];

        actingAs($this->superAdmin)
            ->put(action([DoctorController::class, 'update'], $this->doctor1->id), $data)
            ->assertOk();

        $this->doctor1->refresh();
        expect($this->doctor1->user->name)->toBe('Actualizado')
            ->and($this->doctor1->user->last_name)->toBe('Nombre')
            ->and($this->doctor1->user->phone)->toBe('3009998877');
    });

    it('creates audit log entry when updating doctor', function (): void {
        $oldFaculty = $this->doctor1->faculty;

        $data = [
            'faculty' => 'Updated Faculty',
        ];

        actingAs($this->superAdmin)
            ->put(action([DoctorController::class, 'update'], $this->doctor1->id), $data)
            ->assertOk();

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', Doctor::class)
            ->where('auditable_id', $this->doctor1->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'faculty' => 'No Autorizado',
        ];

        actingAs($this->secretary)
            ->put(action([DoctorController::class, 'update'], $this->doctor1->id), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'faculty' => 'Sin Auth',
        ];

        put(action([DoctorController::class, 'update'], $this->doctor1->id), $data)
            ->assertStatus(401);
    });
});

describe('destroy', function (): void {
    it('deletes doctor successfully when authenticated as super admin', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([DoctorController::class, 'destroy'], $this->doctor1->id))
            ->assertStatus(204);

        $this->doctor1->refresh();
        expect($this->doctor1->trashed())->toBeTrue()
            ->and($this->doctor1->user->status)->toBe(UserStatus::INACTIVE);
    });

    it('deletes doctor successfully when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->delete(action([DoctorController::class, 'destroy'], $this->doctor2->id))
            ->assertStatus(204);

        $this->doctor2->refresh();
        expect($this->doctor2->trashed())->toBeTrue()
            ->and($this->doctor2->user->status)->toBe(UserStatus::INACTIVE);
    });

    it('creates audit log entry when deleting doctor', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([DoctorController::class, 'destroy'], $this->doctor1->id))
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', Doctor::class)
            ->where('auditable_id', $this->doctor1->id)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray()
            ->and($auditLog->new_values)->toBeNull();
    });

    it('excludes deleted doctor from list', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([DoctorController::class, 'destroy'], $this->doctor1->id))
            ->assertStatus(204);

        $response = actingAs($this->superAdmin)
            ->get(action([DoctorController::class, 'index']))
            ->assertOk();

        $doctors = $response->json();
        $doctorIds = collect($doctors)->pluck('id');

        expect($doctorIds)->not->toContain($this->doctor1->id);
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->delete(action([DoctorController::class, 'destroy'], $this->doctor1->id))
            ->assertStatus(403);

        $this->doctor1->refresh();
        expect($this->doctor1->trashed())->toBeFalse();
    });

    it('requires authentication', function (): void {
        delete(action([DoctorController::class, 'destroy'], $this->doctor1->id))
            ->assertStatus(401);

        $this->doctor1->refresh();
        expect($this->doctor1->trashed())->toBeFalse();
    });

    it('returns 404 without JSON when doctor not found for destroy', function (): void {
        $response = actingAs($this->superAdmin)
            ->delete(action([DoctorController::class, 'destroy'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });

    it('returns 404 without JSON when doctor not found for update', function (): void {
        $data = [
            'name' => 'Not Found',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([DoctorController::class, 'update'], 99999), $data);

        $response->assertStatus(404)
            ->assertContent('');
    });
});
