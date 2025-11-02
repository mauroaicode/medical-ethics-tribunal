<?php

declare(strict_types=1);

use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    $this->specialty = MedicalSpecialty::query()->first() ?? MedicalSpecialty::factory()->create();
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();

    $this->doctor1 = Doctor::factory()->create([
        'user_id' => $this->user1->id,
        'specialty_id' => $this->specialty->id,
    ]);

    $this->doctor2 = Doctor::factory()->create([
        'user_id' => $this->user2->id,
        'specialty_id' => $this->specialty->id,
    ]);

    $this->deletedDoctor = Doctor::factory()->create([
        'user_id' => User::factory()->create()->id,
        'specialty_id' => $this->specialty->id,
        'deleted_at' => now(),
    ]);
});

it('includes user relationship correctly', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->withUser()
        ->get();

    expect($doctors)->toHaveCount(2);

    foreach ($doctors as $doctor) {
        expect($doctor->relationLoaded('user'))->toBeTrue()
            ->and($doctor->user)->not->toBeNull();
    }
});

it('includes specialty relationship correctly', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->withSpecialty()
        ->get();

    expect($doctors)->toHaveCount(2);

    foreach ($doctors as $doctor) {
        expect($doctor->relationLoaded('specialty'))->toBeTrue()
            ->and($doctor->specialty)->not->toBeNull();
    }
});

it('includes both user and specialty relationships', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->withRelations()
        ->get();

    expect($doctors)->toHaveCount(2);

    foreach ($doctors as $doctor) {
        expect($doctor->relationLoaded('user'))->toBeTrue()
            ->and($doctor->relationLoaded('specialty'))->toBeTrue();
    }
});

it('excludes soft deleted doctors', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id, $this->deletedDoctor->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->withoutTrashed()
        ->get();

    expect($doctors)->toHaveCount(2)
        ->and($doctors->pluck('id'))->toContain($this->doctor1->id)
        ->and($doctors->pluck('id'))->toContain($this->doctor2->id)
        ->and($doctors->pluck('id'))->not->toContain($this->deletedDoctor->id);
});

it('orders doctors by created_at correctly', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->orderedByCreatedAt()
        ->get();

    $createdAts = $doctors->pluck('created_at')->map(fn ($date) => $date->timestamp)->all();
    $sortedCreatedAts = $createdAts;
    rsort($sortedCreatedAts);

    expect($createdAts)->toBe($sortedCreatedAts);
});

it('can be chained with other query methods', function (): void {
    $doctorIds = [$this->doctor1->id, $this->doctor2->id];

    $doctors = Doctor::query()
        ->whereIn('id', $doctorIds)
        ->withRelations()
        ->withoutTrashed()
        ->orderedByCreatedAt()
        ->get();

    expect($doctors)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->and($doctors)->toHaveCount(2);
});
