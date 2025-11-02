<?php

declare(strict_types=1);

use Src\Domain\City\Models\City;
use Src\Domain\Department\Models\Department;

beforeEach(function (): void {
    // Create test zone first
    $this->zone = Src\Domain\Zone\Models\Zone::query()->firstOrCreate([
        'name' => 'Zona Test',
        'description' => 'Descripción de zona test',
    ]);

    // Create test departments
    $this->department1 = Department::query()->create([
        'codigo' => '19',
        'descripcion' => 'CAUCA',
        'idZona' => $this->zone->id,
    ]);

    $this->department2 = Department::query()->create([
        'codigo' => '05',
        'descripcion' => 'ANTIOQUIA',
        'idZona' => $this->zone->id,
    ]);

    // Create test cities
    $this->city1 = City::query()->create([
        'codigo' => '19001',
        'iddepartamento' => $this->department1->id,
        'descripcion' => 'POPAYÁN',
    ]);

    $this->city2 = City::query()->create([
        'codigo' => '19022',
        'iddepartamento' => $this->department1->id,
        'descripcion' => 'SANTANDER DE QUILICHAO',
    ]);

    $this->city3 = City::query()->create([
        'codigo' => '05001',
        'iddepartamento' => $this->department2->id,
        'descripcion' => 'MEDELLÍN',
    ]);

    $this->city4 = City::query()->create([
        'codigo' => '19013',
        'iddepartamento' => $this->department1->id,
        'descripcion' => 'CALOTO',
    ]);
});

it('filters cities by department correctly', function (): void {
    $cities = City::query()
        ->byDepartment($this->department1->id)
        ->get();

    expect($cities)->toHaveCount(3)
        ->and($cities->pluck('id'))->toContain($this->city1->id)
        ->and($cities->pluck('id'))->toContain($this->city2->id)
        ->and($cities->pluck('id'))->toContain($this->city4->id)
        ->and($cities->pluck('id'))->not->toContain($this->city3->id);
});

it('excludes cities from other departments', function (): void {
    $cities = City::query()
        ->byDepartment($this->department1->id)
        ->get();

    $antioquiaCityIds = collect([$this->city3->id]);

    foreach ($cities as $city) {
        expect($antioquiaCityIds)->not->toContain($city->id);
    }
});

it('orders cities by description correctly', function (): void {
    $cities = City::query()
        ->whereIn('id', [$this->city1->id, $this->city2->id, $this->city3->id, $this->city4->id])
        ->orderedByDescription()
        ->get();

    $descriptions = $cities->pluck('descripcion')->toArray();
    $sortedDescriptions = $descriptions;
    sort($sortedDescriptions);

    expect($descriptions)->toBe($sortedDescriptions);
});

it('can chain byDepartment with orderedByDescription', function (): void {
    $cities = City::query()
        ->byDepartment($this->department1->id)
        ->orderedByDescription()
        ->get();

    expect($cities)->toHaveCount(3);

    $descriptions = $cities->pluck('descripcion')->toArray();
    $sortedDescriptions = $descriptions;
    sort($sortedDescriptions);

    expect($descriptions)->toBe($sortedDescriptions)
        ->and($cities->first()->iddepartamento)->toBe($this->department1->id);
});

it('returns empty collection when filtering by non-existent department', function (): void {
    $cities = City::query()
        ->byDepartment(99999)
        ->get();

    expect($cities)->toBeEmpty();
});

it('returns builder instance for further chaining', function (): void {
    $query = City::query()->byDepartment($this->department1->id);

    expect($query)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
});

it('can filter multiple departments separately', function (): void {
    $caucaCities = City::query()
        ->byDepartment($this->department1->id)
        ->get();

    $antioquiaCities = City::query()
        ->byDepartment($this->department2->id)
        ->get();

    expect($caucaCities)->toHaveCount(3)
        ->and($antioquiaCities)->toHaveCount(1)
        ->and($caucaCities->pluck('id'))->not->toContain($this->city3->id)
        ->and($antioquiaCities->pluck('id'))->toContain($this->city3->id);
});
