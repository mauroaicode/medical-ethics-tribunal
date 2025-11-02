<?php

declare(strict_types=1);

use Spatie\Permission\Models\Role;
use Src\Application\Admin\City\Controllers\CityController;
use Src\Domain\City\Models\City;
use Src\Domain\Department\Models\Department;
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

    // Find or create CAUCA department
    $this->caucaDepartment = Department::query()
        ->where('descripcion', config('app.default_department', 'CAUCA'))
        ->first();

    if (! $this->caucaDepartment) {
        // Create CAUCA department if it doesn't exist
        $this->caucaDepartment = Department::query()->create([
            'codigo' => '19',
            'descripcion' => 'CAUCA',
            'idZona' => 1,
        ]);
    }

    // Create test cities for CAUCA
    $this->city1 = City::query()->create([
        'codigo' => '19001',
        'iddepartamento' => $this->caucaDepartment->id,
        'descripcion' => 'POPAYÁN',
    ]);

    $this->city2 = City::query()->create([
        'codigo' => '19022',
        'iddepartamento' => $this->caucaDepartment->id,
        'descripcion' => 'SANTANDER DE QUILICHAO',
    ]);

    // Create a city from another department to ensure filtering works
    $otherDepartment = Department::query()
        ->where('descripcion', '!=', 'CAUCA')
        ->first();

    if ($otherDepartment) {
        $this->otherCity = City::query()->create([
            'codigo' => '05001',
            'iddepartamento' => $otherDepartment->id,
            'descripcion' => 'MEDELLÍN',
        ]);
    }
});

describe('index', function (): void {
    it('returns list of cities from CAUCA department when authenticated as super admin', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([CityController::class, 'index']))
            ->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'codigo',
                    'descripcion',
                    'department_id',
                ],
            ]);

        $cities = $response->json();

        expect($cities)->not->toBeEmpty();

        $cityIds = collect($cities)->pluck('id')->toArray();

        expect($cityIds)->toContain($this->city1->id)
            ->and($cityIds)->toContain($this->city2->id);

        // Verify that cities from other departments are not included
        if (isset($this->otherCity)) {
            expect($cityIds)->not->toContain($this->otherCity->id);
        }

        // Verify all cities belong to CAUCA department
        foreach ($cities as $city) {
            expect($city['department_id'])->toBe($this->caucaDepartment->id);
        }
    });

    it('returns list of cities from CAUCA department when authenticated as admin', function (): void {
        $response = actingAs($this->admin)
            ->get(action([CityController::class, 'index']))
            ->assertOk();

        $cities = $response->json();

        expect($cities)->not->toBeEmpty();

        foreach ($cities as $city) {
            expect($city['department_id'])->toBe($this->caucaDepartment->id);
        }
    });

    it('returns cities ordered by description', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([CityController::class, 'index']))
            ->assertOk();

        $cities = $response->json();
        $descriptions = collect($cities)->pluck('descripcion')->toArray();
        $sortedDescriptions = $descriptions;
        sort($sortedDescriptions);

        expect($descriptions)->toBe($sortedDescriptions);
    });

    it('requires authentication', function (): void {
        get(action([CityController::class, 'index']))
            ->assertStatus(401)
            ->assertJson([
                'messages' => [__('auth.unauthorized')],
                'code' => 401,
            ]);
    });

    it('returns cities in consistent format', function (): void {
        $response = actingAs($this->superAdmin)
            ->get(action([CityController::class, 'index']))
            ->assertOk();

        $cities = $response->json();

        foreach ($cities as $city) {
            expect($city)->toHaveKeys(['id', 'codigo', 'descripcion', 'department_id'])
                ->and($city['id'])->toBeInt()
                ->and($city['codigo'])->toBeString()
                ->and($city['descripcion'])->toBeString()
                ->and($city['department_id'])->toBeInt()
                ->and($city['codigo'])->not->toBeEmpty()
                ->and($city['descripcion'])->not->toBeEmpty();
        }
    });

    it('returns empty array when CAUCA department does not exist', function (): void {
        // Temporarily change config to non-existent department
        config(['app.default_department' => 'NON_EXISTENT_DEPARTMENT']);

        $response = actingAs($this->superAdmin)
            ->get(action([CityController::class, 'index']))
            ->assertOk();

        $cities = $response->json();

        expect($cities)->toBeArray()
            ->and($cities)->toBeEmpty();
    });
});
