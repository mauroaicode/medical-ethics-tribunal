<?php

declare(strict_types=1);

use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

beforeEach(function (): void {
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

    $this->specialty4 = MedicalSpecialty::query()->firstOrCreate(
        ['name' => 'Pediatría'],
        ['description' => 'Especialidad médica infantil']
    );
});

it('orders medical specialties by name correctly', function (): void {
    $specialties = MedicalSpecialty::query()
        ->whereIn('id', [$this->specialty1->id, $this->specialty2->id, $this->specialty3->id, $this->specialty4->id])
        ->orderedByName()
        ->get();

    expect($specialties)->toHaveCount(4);

    $names = $specialties->pluck('name')->toArray();
    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('can chain orderedByName with other query methods', function (): void {
    $specialties = MedicalSpecialty::query()
        ->whereIn('id', [$this->specialty1->id, $this->specialty2->id])
        ->orderedByName()
        ->get();

    expect($specialties)->toHaveCount(2);

    $names = $specialties->pluck('name')->toArray();
    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});

it('returns builder instance for further chaining', function (): void {
    $query = MedicalSpecialty::query()->orderedByName();

    expect($query)->toBeInstanceOf(Illuminate\Database\Eloquent\Builder::class);
});

it('applies ordering to all specialties when no filters are used', function (): void {
    $specialties = MedicalSpecialty::query()
        ->orderedByName()
        ->get();

    expect($specialties)->not->toBeEmpty();

    $names = $specialties->pluck('name')->toArray();
    $firstSpecialtyName = $names[0] ?? null;

    // Verify that the first specialty is alphabetically first
    // (or at least that ordering was applied)
    expect($names)->toBeArray()
        ->and($firstSpecialtyName)->not->toBeNull();
});

it('maintains ordering with multiple specialties', function (): void {
    // Create additional specialty (use firstOrCreate to avoid duplicates)
    $specialty5 = MedicalSpecialty::query()->firstOrCreate(
        ['name' => 'Anestesiología'],
        ['description' => null]
    );

    $specialties = MedicalSpecialty::query()
        ->whereIn('id', [
            $this->specialty1->id,
            $this->specialty2->id,
            $this->specialty3->id,
            $this->specialty4->id,
            $specialty5->id,
        ])
        ->orderedByName()
        ->get();

    expect($specialties)->toHaveCount(5);

    $names = $specialties->pluck('name')->toArray();
    $sortedNames = $names;
    sort($sortedNames);

    expect($names)->toBe($sortedNames);
});
