<?php

declare(strict_types=1);

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Src\Application\Admin\Process\Controllers\ProcessController;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Enums\ProcessStatus;
use Src\Domain\Process\Models\Process;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {

    $this->superAdminRole = Role::firstOrCreate(['name' => UserRole::SUPER_ADMIN->value, 'guard_name' => 'web']);
    $this->adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value, 'guard_name' => 'web']);
    $this->secretaryRole = Role::firstOrCreate(['name' => UserRole::SECRETARY->value, 'guard_name' => 'web']);

    $this->superAdmin = User::factory()->create();
    $this->superAdmin->assignRole($this->superAdminRole);

    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    $this->secretary = User::factory()->create();
    $this->secretary->assignRole($this->secretaryRole);

    $this->complainant = Complainant::factory()->create();
    $this->doctor = Doctor::factory()->create();
    $this->magistrate1 = Magistrate::factory()->create();
    $this->magistrate2 = Magistrate::factory()->create();

    $this->process1 = Process::factory()->create([
        'complainant_id' => $this->complainant->id,
        'doctor_id' => $this->doctor->id,
        'magistrate_instructor_id' => $this->magistrate1->id,
        'magistrate_ponente_id' => $this->magistrate2->id,
    ]);
    $this->process2 = Process::factory()->create([
        'complainant_id' => $this->complainant->id,
        'doctor_id' => $this->doctor->id,
        'magistrate_instructor_id' => $this->magistrate1->id,
        'magistrate_ponente_id' => $this->magistrate2->id,
    ]);
});

describe('index', function (): void {
    it('returns list of processes when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ProcessController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'complainant_id',
                    'doctor_id',
                    'magistrate_instructor_id',
                    'magistrate_ponente_id',
                    'name',
                    'process_number',
                    'start_date',
                    'status',
                    'description',
                    'complainant',
                    'doctor',
                    'magistrate_instructor',
                    'magistrate_ponente',
                ],
            ]);

        $processes = $response->json();
        $processIds = collect($processes)->pluck('id');

        expect($processIds)->toContain($this->process1->id)
            ->and($processIds)->toContain($this->process2->id);
    });

    it('returns list of processes when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([ProcessController::class, 'index']));

        $response->assertOk();

        $processes = $response->json();
        $processIds = collect($processes)->pluck('id');

        expect($processIds)->toContain($this->process1->id)
            ->and($processIds)->toContain($this->process2->id);
    });

    it('returns list of processes when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([ProcessController::class, 'index']))
            ->assertOk();

        $processes = $response->json();
        $processIds = collect($processes)->pluck('id');

        expect($processIds)->toContain($this->process1->id)
            ->and($processIds)->toContain($this->process2->id);
    });

    it('requires authentication', function (): void {
        get(action([ProcessController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });
});

describe('show', function (): void {
    it('returns process details when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ProcessController::class, 'show'], $this->process1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'complainant_id',
                'doctor_id',
                'magistrate_instructor_id',
                'magistrate_ponente_id',
                'name',
                'process_number',
                'start_date',
                'status',
                'description',
                'complainant',
                'doctor',
                'magistrate_instructor',
                'magistrate_ponente',
                'template_documents',
                'proceedings',
            ]);

        expect($response->json('id'))->toBe($this->process1->id);
    });

    it('returns process details when authenticated as admin', function (): void {
        actingAs($this->admin)
            ->get(action([ProcessController::class, 'show'], $this->process1->id))
            ->assertOk();
    });

    it('returns process details when authenticated as secretary', function (): void {
        actingAs($this->secretary)
            ->get(action([ProcessController::class, 'show'], $this->process1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'complainant_id',
                'doctor_id',
                'magistrate_instructor_id',
                'magistrate_ponente_id',
                'name',
                'process_number',
                'start_date',
                'status',
                'description',
                'complainant',
                'doctor',
                'magistrate_instructor',
                'magistrate_ponente',
                'template_documents',
                'proceedings',
            ]);
    });

    it('requires authentication', function (): void {
        get(action([ProcessController::class, 'show'], $this->process1->id))
            ->assertStatus(401);
    });

    it('returns 404 without JSON when process not found', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([ProcessController::class, 'show'], 99999));

        $response->assertStatus(404)
            ->assertContent('');
    });

    it('includes proceedings when they exist', function (): void {
        Storage::fake('public');

        // Create proceedings for the process
        $proceeding1 = Proceeding::factory()->create([
            'process_id' => $this->process1->id,
            'proceeding_date' => now()->subDays(5),
        ]);
        $proceeding1->addMedia(UploadedFile::fake()->create('test1.pdf', 500, 'application/pdf'))
            ->toMediaCollection(FileType::PROCEEDING_DOCUMENT->value);

        $proceeding2 = Proceeding::factory()->create([
            'process_id' => $this->process1->id,
            'proceeding_date' => now()->subDays(2),
        ]);
        $proceeding2->addMedia(UploadedFile::fake()->create('test2.pdf', 600, 'application/pdf'))
            ->toMediaCollection(FileType::PROCEEDING_DOCUMENT->value);

        $response = actingAs($this->superAdmin)
            ->get(action([ProcessController::class, 'show'], $this->process1->id))
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'proceedings' => [
                    '*' => [
                        'id',
                        'process_id',
                        'name',
                        'description',
                        'proceeding_date',
                        'file',
                    ],
                ],
            ]);

        $proceedings = $response->json('proceedings');
        expect($proceedings)->toBeArray()
            ->and(count($proceedings))->toBe(2)
            ->and($proceedings[0]['id'])->toBe($proceeding2->id) // Most recent first
            ->and($proceedings[1]['id'])->toBe($proceeding1->id);
    });
});

describe('store', function (): void {
    it('creates process successfully when authenticated as super admin', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Nuevo Proceso',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción del nuevo proceso',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'complainant_id',
                'doctor_id',
                'magistrate_instructor_id',
                'magistrate_ponente_id',
                'name',
                'process_number',
                'start_date',
                'status',
                'description',
            ]);

        expect($response->json('name'))->toBe('Nuevo Proceso')
            ->and($response->json('status'))->toBe(ProcessStatus::DRAFT->value)
            ->and($response->json('process_number'))->toMatch('/^PRO-\d{4}$/');
    });

    it('creates process successfully when authenticated as admin', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Otro Proceso',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción del otro proceso',
        ];

        $response = actingAs($this->admin)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(201);

        $createdProcess = Process::query()->where('name', 'Otro Proceso')->first();
        expect($createdProcess)->not->toBeNull()
            ->and($createdProcess->status)->toBe(ProcessStatus::DRAFT);
    });

    it('always creates process with draft status regardless of input', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Proceso con Status Predefinido',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción del proceso',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(201);

        $createdProcess = Process::query()->where('name', 'Proceso con Status Predefinido')->first();

        expect($response->json('status'))->toBe(ProcessStatus::DRAFT->value)
            ->and($createdProcess->status)->toBe(ProcessStatus::DRAFT);
    });

    it('creates audit log entry when creating process', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Proceso con Auditoría',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción del proceso con auditoría',
        ];

        $response = actingAs($this->superAdmin)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(201);

        $processId = $response->json('id');

        $auditLog = AuditLog::query()
            ->where('action', 'create')
            ->where('auditable_type', Process::class)
            ->where('auditable_id', $processId)
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeNull()
            ->and($auditLog->new_values)->toBeArray();
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'No Autorizado',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción',
        ];

        actingAs($this->secretary)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Test',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción',
        ];

        post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(401);
    });

    it('fails validation with missing required fields', function (): void {
        actingAs($this->superAdmin)
            ->post(action([ProcessController::class, 'store']), [])
            ->assertStatus(422);
    });

    it('fails validation with invalid complainant_id', function (): void {
        $data = [
            'complainant_id' => 99999,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
            'name' => 'Test',
            'start_date' => now()->format('Y-m-d'),
            'description' => 'Descripción',
        ];

        actingAs($this->superAdmin)
            ->post(action([ProcessController::class, 'store']), $data)
            ->assertStatus(422);
    });
});

describe('update', function (): void {
    it('updates process successfully when authenticated as super admin', function (): void {
        // Verify step-up code first
        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.update";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $data = [
            'name' => 'Proceso Actualizado',
            'status' => ProcessStatus::IN_PROGRESS->value,
        ];

        actingAs($this->superAdmin)
            ->put(action([ProcessController::class, 'update'], $this->process1->id), $data)
            ->assertStatus(200);

        $this->process1->refresh();

        expect($this->process1->name)->toBe('Proceso Actualizado')
            ->and($this->process1->status)->toBe(ProcessStatus::IN_PROGRESS);
    });

    it('updates process successfully when authenticated as admin', function (): void {
        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->admin->id}_process.update";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $data = [
            'description' => 'Nueva descripción',
        ];

        actingAs($this->admin)
            ->put(action([ProcessController::class, 'update'], $this->process1->id), $data)
            ->assertStatus(200);

        $this->process1->refresh();

        expect($this->process1->description)->toBe('Nueva descripción');
    });

    it('creates audit log entry when updating process', function (): void {
        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.update";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $this->process1->getAttributes();

        $data = [
            'description' => 'Descripción Actualizada',
        ];

        actingAs($this->superAdmin)
            ->put(action([ProcessController::class, 'update'], $this->process1->id), $data)
            ->assertStatus(200);

        $auditLog = AuditLog::query()
            ->where('action', 'update')
            ->where('auditable_type', Process::class)
            ->where('auditable_id', $this->process1->id)
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
            ->put(action([ProcessController::class, 'update'], $this->process1->id), $data)
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        $data = [
            'name' => 'Test',
        ];

        put(action([ProcessController::class, 'update'], $this->process1->id), $data)
            ->assertStatus(401);
    });

    it('returns 404 without JSON when process not found', function (): void {
        $data = [
            'name' => 'Test',
        ];

        actingAs($this->superAdmin)
            ->put(action([ProcessController::class, 'update'], 99999), $data)
            ->assertStatus(404)
            ->assertContent('');
    });
});

describe('destroy', function (): void {
    it('deletes process successfully when authenticated as super admin', function (): void {
        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id), [
                'deleted_reason' => 'Proceso duplicado',
            ])
            ->assertStatus(204);

        $deletedProcess = Process::withTrashed()->find($processToDelete->id);
        expect(Process::query()->find($processToDelete->id))->toBeNull()
            ->and($deletedProcess)->not->toBeNull()
            ->and($deletedProcess->deleted_reason)->toBe('Proceso duplicado');
    });

    it('deletes process successfully when authenticated as admin', function (): void {
        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->admin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        actingAs($this->admin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id), [
                'deleted_reason' => 'Error en el proceso',
            ])
            ->assertStatus(204);

        $deletedProcess = Process::withTrashed()->find($processToDelete->id);
        expect($deletedProcess->deleted_reason)->toBe('Error en el proceso');
    });

    it('creates audit log entry when deleting process', function (): void {
        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        $processToDelete->getAttributes();

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id), [
                'deleted_reason' => 'Proceso cerrado por resolución',
            ])
            ->assertStatus(204);

        $auditLog = AuditLog::query()
            ->where('action', 'delete')
            ->where('auditable_type', Process::class)
            ->where('auditable_id', $processToDelete->id)
            ->latest()
            ->first();

        expect($auditLog)->not->toBeNull()
            ->and($auditLog->user_id)->toBe($this->superAdmin->id)
            ->and($auditLog->old_values)->toBeArray();

        $deletedProcess = Process::withTrashed()->find($processToDelete->id);
        expect($deletedProcess->deleted_reason)->toBe('Proceso cerrado por resolución');
    });

    it('fails when authenticated as secretary (unauthorized)', function (): void {
        actingAs($this->secretary)
            ->delete(action([ProcessController::class, 'destroy'], $this->process1->id), [
                'deleted_reason' => 'Test reason',
            ])
            ->assertStatus(403);
    });

    it('requires authentication', function (): void {
        delete(action([ProcessController::class, 'destroy'], $this->process1->id), [
            'deleted_reason' => 'Test reason',
        ])
            ->assertStatus(401);
    });

    it('returns 404 without JSON when process not found', function (): void {
        actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], 99999), [
                'deleted_reason' => 'Test reason',
            ])
            ->assertStatus(404)
            ->assertContent('');
    });

    it('requires deleted_reason when deleting process', function (): void {

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        $response = actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id))
            ->assertStatus(422)
            ->assertJsonStructure([
                'messages',
            ]);

        $messages = $response->json('messages');
        expect($messages)->toBeArray()
            ->and($messages)->toContain(__('validation.required', ['attribute' => __('data.deleted_reason')]));
    });

    it('validates deleted_reason minimum length', function (): void {

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id), [
                'deleted_reason' => 'AB',
            ])
            ->assertStatus(422);
    });

    it('validates deleted_reason maximum length', function (): void {

        $verificationKey = config('step-up.verification.cache_key_prefix')."_{$this->superAdmin->id}_process.delete";
        Cache::put($verificationKey, true, now()->addMinutes(10));

        $processToDelete = Process::factory()->create([
            'complainant_id' => $this->complainant->id,
            'doctor_id' => $this->doctor->id,
            'magistrate_instructor_id' => $this->magistrate1->id,
            'magistrate_ponente_id' => $this->magistrate2->id,
        ]);

        actingAs($this->superAdmin)
            ->delete(action([ProcessController::class, 'destroy'], $processToDelete->id), [
                'deleted_reason' => str_repeat('a', 1001),
            ])
            ->assertStatus(422);
    });
});
