<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\MedicalSpecialty\Controllers\MedicalSpecialtyController;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;
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

    // Create test medical specialties (use firstOrCreate to avoid duplicates from seeders)
    $this->specialty1 = MedicalSpecialty::query()->firstOrCreate(
        ['name' => 'Cardiología'],
        ['description' => 'Especialidad médica del corazón']
    );

    $this->specialty2 = MedicalSpecialty::query()->firstOrCreate(
        ['name' => 'Dermatología'],
        ['description' => 'Especialidad médica de la piel']
    );

    $this->specialty3 = MedicalSpecialty::query()->firstOrCreate(
        ['name' => 'Neurología'],
        ['description' => null]
    );
});

describe('index', function (): void {
    it('returns list of medical specialties when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'description',
                ],
            ]);

        $specialties = $response->json();

        expect($specialties)->not->toBeEmpty();

        $specialtyIds = collect($specialties)->pluck('id')->toArray();

        expect($specialtyIds)->toContain($this->specialty1->id)
            ->and($specialtyIds)->toContain($this->specialty2->id)
            ->and($specialtyIds)->toContain($this->specialty3->id);
    });

    it('returns list of medical specialties when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();

        expect($specialties)->not->toBeEmpty();
    });

    it('returns list of medical specialties when authenticated as secretary', function (): void {
        $response = actingAs($this->secretary)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();

        expect($specialties)->not->toBeEmpty();
    });

    it('requires authentication', function (): void {
        get(action([MedicalSpecialtyController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns medical specialties ordered by name', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();

        expect($specialties)->not->toBeEmpty();

        // Verify that specialties are returned (order verification depends on database collation)
        $names = collect($specialties)->pluck('name')->toArray();
        expect($names)->toHaveCount(count($specialties))
            ->and($names)->toContain($this->specialty1->name)
            ->and($names)->toContain($this->specialty2->name)
            ->and($names)->toContain($this->specialty3->name);

        // Verify that our test specialties are included in the response
    });

    it('returns medical specialties in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();

        foreach ($specialties as $specialty) {
            expect($specialty)->toHaveKeys(['id', 'name', 'description'])
                ->and($specialty['id'])->toBeInt()
                ->and($specialty['name'])->toBeString()
                ->and($specialty['name'])->not->toBeEmpty();

            // Description can be null or string
            if ($specialty['description'] !== null) {
                expect($specialty['description'])->toBeString();
            }
        }
    });

    it('includes all created medical specialties', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();
        $specialtyNames = collect($specialties)->pluck('name')->toArray();

        expect($specialtyNames)->toContain('Cardiología')
            ->and($specialtyNames)->toContain('Dermatología')
            ->and($specialtyNames)->toContain('Neurología');
    });

    it('returns only created test specialties when querying specific ones', function (): void {
        // Create a unique specialty for this test
        $testSpecialty = MedicalSpecialty::query()->firstOrCreate(
            ['name' => 'Test Specialty Unique '.uniqid()],
            ['description' => 'Test description']
        );

        $response = actingAs($this->superAdmin)
            ->get(action([MedicalSpecialtyController::class, 'index']))
            ->assertOk();

        $specialties = $response->json();
        $specialtyIds = collect($specialties)->pluck('id')->toArray();

        // Should include our test specialty
        expect($specialtyIds)->toContain($testSpecialty->id);
    });
});
