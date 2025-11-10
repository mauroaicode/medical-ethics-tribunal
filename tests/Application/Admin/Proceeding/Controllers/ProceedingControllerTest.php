<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Src\Application\Admin\Proceeding\Controllers\ProceedingController;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    Storage::fake('public');

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

    // Create process for testing
    $this->complainant = Complainant::factory()->create();
    $this->doctor = Doctor::factory()->create();
    $this->magistrate1 = Magistrate::factory()->create();
    $this->magistrate2 = Magistrate::factory()->create();

    $this->process = Process::factory()->create([
        'complainant_id' => $this->complainant->id,
        'doctor_id' => $this->doctor->id,
        'magistrate_instructor_id' => $this->magistrate1->id,
        'magistrate_ponente_id' => $this->magistrate2->id,
    ]);

    // Create proceedings for testing
    $this->proceeding1 = Proceeding::factory()->create(['process_id' => $this->process->id]);
    $this->proceeding2 = Proceeding::factory()->create(['process_id' => $this->process->id]);
});

describe('index', function (): void {
    it('returns list of proceedings for a process when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ProceedingController::class, 'index'], $this->process->id))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'process_id',
                    'name',
                    'description',
                    'proceeding_date',
                    'process',
                    'file',
                ],
            ]);

        $proceedings = $response->json();
        $proceedingIds = collect($proceedings)->pluck('id');

        expect($proceedingIds)->toContain($this->proceeding1->id)
            ->and($proceedingIds)->toContain($this->proceeding2->id);
    });

    it('returns list of proceedings when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([ProceedingController::class, 'index'], $this->process->id));

        $response->assertOk();

        $proceedings = $response->json();
        $proceedingIds = collect($proceedings)->pluck('id');

        expect($proceedingIds)->toContain($this->proceeding1->id)
            ->and($proceedingIds)->toContain($this->proceeding2->id);
    });

    it('returns list of proceedings when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([ProceedingController::class, 'index'], $this->process->id))
            ->assertOk();

        $proceedings = $response->json();
        $proceedingIds = collect($proceedings)->pluck('id');

        expect($proceedingIds)->toContain($this->proceeding1->id)
            ->and($proceedingIds)->toContain($this->proceeding2->id);
    });

    it('requires authentication', function (): void {
        get(action([ProceedingController::class, 'index'], $this->process->id))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns 404 without JSON when process not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ProceedingController::class, 'index'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });
});

describe('store', function (): void {
    it('creates proceeding successfully when authenticated as super admin', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Nueva Actuación',
            'description' => 'Descripción de la nueva actuación',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'process_id',
                'name',
                'description',
                'proceeding_date',
                'process',
                'file',
            ]);

        expect($response->json('name'))->toBe('Nueva Actuación')
            ->and($response->json('process_id'))->toBe($this->process->id);

        $createdProceeding = Proceeding::query()->find($response->json('id'));
        expect($createdProceeding)->not->toBeNull()
            ->and($createdProceeding->getFirstMedia('proceeding_document'))->not->toBeNull();
    });

    it('creates proceeding successfully when authenticated as admin', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Otra Actuación',
            'description' => 'Descripción de otra actuación',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        $response = actingAs($this->admin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(201);

        $createdProceeding = Proceeding::query()->where('name', 'Otra Actuación')->first();
        expect($createdProceeding)->not->toBeNull();
    });

    it('creates audit log entry when creating proceeding', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Actuación con Auditoría',
            'description' => 'Descripción de la actuación con auditoría',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(201);

        $proceedingId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', Proceeding::class)
            ->where('auditable_id', $proceedingId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'No Autorizado',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        actingAs($this->secretary)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Test',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(401);
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), [])
            ->assertStatus(422);
    });

    it('fails validation with invalid process_id', function (): void {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $data = [
            'process_id' => 99999,
            'name' => 'Test',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with missing PDF file', function (): void {
        $data = [
            'process_id' => $this->process->id,
            'name' => 'Test',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
        ];

        actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with invalid file type (not PDF)', function (): void {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Test',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(422);
    });

    it('fails validation with file too large', function (): void {
        $file = UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'); // > 10MB

        $data = [
            'process_id' => $this->process->id,
            'name' => 'Test',
            'description' => 'Descripción',
            'proceeding_date' => now()->format('Y-m-d'),
            'file' => $file,
        ];

        actingAs($this->superAdmin)
            ->post(action([ProceedingController::class, 'store']), $data)
            ->assertStatus(422);
    });
});

describe('update', function (): void {
    it('updates proceeding successfully when authenticated as super admin', function (): void {
        $data = [
            'name' => 'Actuación Actualizada',
            'description' => 'Descripción actualizada',
        ];

        actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(200);

        $this->proceeding1->refresh();

        expect($this->proceeding1->name)->toBe('Actuación Actualizada')
            ->and($this->proceeding1->description)->toBe('Descripción actualizada');
    });

    it('updates proceeding successfully when authenticated as admin', function (): void {
        $data = [
            'proceeding_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        actingAs($this->admin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(200);

        $this->proceeding1->refresh();

        expect($this->proceeding1->proceeding_date->format('Y-m-d'))->toBe($data['proceeding_date']);
    });

    it('updates proceeding with new PDF file', function (): void {
        // Add initial file
        $initialFile = UploadedFile::fake()->create('initial.pdf', 100, 'application/pdf');
        $this->proceeding1->addMedia($initialFile->getPathname())
            ->usingName('initial.pdf')
            ->toMediaCollection('proceeding_document');

        $newFile = UploadedFile::fake()->create('updated.pdf', 100, 'application/pdf');

        $data = [
            'file' => $newFile,
        ];

        actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(200);

        $this->proceeding1->refresh();
        $media = $this->proceeding1->getFirstMedia('proceeding_document');

        expect($media)->not->toBeNull()
            ->and($media->file_name)->toBe('updated.pdf');
    });

    it('creates audit log entry when updating proceeding', function (): void {
        $oldValues = $this->proceeding1->getAttributes();

        $data = [
            'description' => 'Descripción Actualizada',
        ];

        $response = actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(200);

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', Proceeding::class)
            ->where('auditable_id', $this->proceeding1->id)
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
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Test',
        ];

        put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(401);
    });

    it('returns 404 without JSON when proceeding not found', function (): void {
        $data = [
            'name' => 'Test',
        ];

        actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], 99999), $data)
            ->assertStatus(404)
            ->assertContent('');
    });

    it('fails validation with invalid file type (not PDF)', function (): void {
        $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $data = [
            'file' => $file,
        ];

        actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(422);
    });

    it('fails validation with file too large', function (): void {
        $file = UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'); // > 10MB

        $data = [
            'file' => $file,
        ];

        actingAs($this->superAdmin)
            ->put(action([ProceedingController::class, 'update'], $this->proceeding1->id), $data)
            ->assertStatus(422);
    });
});

describe('destroy', function (): void {
    it('deletes proceeding successfully when authenticated as super admin', function (): void {
        $proceedingToDelete = Proceeding::factory()->create(['process_id' => $this->process->id]);

        // Add file to proceeding
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $proceedingToDelete->addMedia($file->getPathname())
            ->usingName('test.pdf')
            ->toMediaCollection('proceeding_document');

        actingAs($this->superAdmin)
            ->delete(action([ProceedingController::class, 'destroy'], $proceedingToDelete->id))
            ->assertStatus(204);

        expect(Proceeding::query()->find($proceedingToDelete->id))->toBeNull();
    });

    it('deletes proceeding successfully when authenticated as admin', function (): void {
        $proceedingToDelete = Proceeding::factory()->create(['process_id' => $this->process->id]);

        actingAs($this->admin)
            ->delete(action([ProceedingController::class, 'destroy'], $proceedingToDelete->id))
            ->assertStatus(204);
    });

    it('deletes associated media files when deleting proceeding', function (): void {
        $proceedingToDelete = Proceeding::factory()->create(['process_id' => $this->process->id]);

        // Add file to proceeding
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');
        $media = $proceedingToDelete->addMedia($file->getPathname())
            ->usingName('test.pdf')
            ->toMediaCollection('proceeding_document');

        $mediaId = $media->id;

        actingAs($this->superAdmin)
            ->delete(action([ProceedingController::class, 'destroy'], $proceedingToDelete->id))
            ->assertStatus(204);

        expect(Spatie\MediaLibrary\MediaCollections\Models\Media::query()->find($mediaId))->toBeNull();
    });

    it('creates audit log entry when deleting proceeding', function (): void {
        $proceedingToDelete = Proceeding::factory()->create(['process_id' => $this->process->id]);
        $oldValues = $proceedingToDelete->getAttributes();

        actingAs($this->superAdmin)
            ->delete(action([ProceedingController::class, 'destroy'], $proceedingToDelete->id))
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', Proceeding::class)
            ->where('auditable_id', $proceedingToDelete->id)
            ->latest()
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->delete(action([ProceedingController::class, 'destroy'], $this->proceeding1->id))
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        delete(action([ProceedingController::class, 'destroy'], $this->proceeding1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when proceeding not found', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([ProceedingController::class, 'destroy'], 99999))
            ->assertStatus(404)
            ->assertContent('');
    });
});
