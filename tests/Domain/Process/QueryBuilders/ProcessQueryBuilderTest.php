<?php

declare(strict_types=1);

use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Models\Process;
use Src\Domain\User\Models\User;

beforeEach(function (): void {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->user3 = User::factory()->create();
    $this->user4 = User::factory()->create();

    $this->complainant = Complainant::factory()->create(['user_id' => $this->user1->id]);
    $this->doctor = Doctor::factory()->create(['user_id' => $this->user2->id]);
    $this->magistrate1 = Magistrate::factory()->create(['user_id' => $this->user3->id]);
    $this->magistrate2 = Magistrate::factory()->create(['user_id' => $this->user4->id]);

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

    $this->deletedProcess = Process::factory()->create([
        'complainant_id' => $this->complainant->id,
        'doctor_id' => $this->doctor->id,
        'magistrate_instructor_id' => $this->magistrate1->id,
        'magistrate_ponente_id' => $this->magistrate2->id,
    ]);
    $this->deletedProcess->delete();
});

it('includes complainant relationship correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withComplainant()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('complainant'))->toBeTrue()
        ->and($process->complainant)->not->toBeNull();
});

it('includes doctor relationship correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withDoctor()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('doctor'))->toBeTrue()
        ->and($process->doctor)->not->toBeNull();
});

it('includes magistrate instructor relationship correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withMagistrateInstructor()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('magistrateInstructor'))->toBeTrue()
        ->and($process->magistrateInstructor)->not->toBeNull();
});

it('includes magistrate ponente relationship correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withMagistratePonente()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('magistratePonente'))->toBeTrue()
        ->and($process->magistratePonente)->not->toBeNull();
});

it('includes template documents relationship correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withTemplateDocuments()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('templateDocuments'))->toBeTrue();
});

it('includes all relationships correctly', function (): void {
    $process = Process::query()
        ->where('id', $this->process1->id)
        ->withRelations()
        ->first();

    expect($process)->not->toBeNull()
        ->and($process->relationLoaded('complainant'))->toBeTrue()
        ->and($process->relationLoaded('doctor'))->toBeTrue()
        ->and($process->relationLoaded('magistrateInstructor'))->toBeTrue()
        ->and($process->relationLoaded('magistratePonente'))->toBeTrue()
        ->and($process->relationLoaded('templateDocuments'))->toBeTrue();

    if ($process->complainant) {
        expect($process->complainant->relationLoaded('user'))->toBeTrue()
            ->and($process->complainant->relationLoaded('city'))->toBeTrue();
    }

    if ($process->doctor) {
        expect($process->doctor->relationLoaded('user'))->toBeTrue()
            ->and($process->doctor->relationLoaded('specialty'))->toBeTrue();
    }

    if ($process->magistrateInstructor) {
        expect($process->magistrateInstructor->relationLoaded('user'))->toBeTrue();
    }

    if ($process->magistratePonente) {
        expect($process->magistratePonente->relationLoaded('user'))->toBeTrue();
    }
});

it('excludes soft deleted processes', function (): void {
    $processIds = [$this->process1->id, $this->process2->id, $this->deletedProcess->id];

    $processes = Process::query()
        ->whereIn('id', $processIds)
        ->withoutTrashed()
        ->get();

    expect($processes)->toHaveCount(2)
        ->and($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id)
        ->and($processes->pluck('id'))->not->toContain($this->deletedProcess->id);
});

it('orders processes by created_at correctly', function (): void {
    $processIds = [$this->process1->id, $this->process2->id];

    $processes = Process::query()
        ->whereIn('id', $processIds)
        ->orderedByCreatedAt()
        ->get();

    $createdAts = $processes->pluck('created_at')->map(fn ($date) => $date->timestamp)->all();
    $sortedCreatedAts = $createdAts;
    rsort($sortedCreatedAts);

    expect($createdAts)->toBe($sortedCreatedAts);
});

it('can be chained with other query methods', function (): void {
    $processIds = [$this->process1->id, $this->process2->id];

    $processes = Process::query()
        ->whereIn('id', $processIds)
        ->withRelations()
        ->withoutTrashed()
        ->orderedByCreatedAt()
        ->get();

    expect($processes)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->and($processes)->toHaveCount(2);
});
