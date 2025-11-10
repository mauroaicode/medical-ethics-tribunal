<?php

declare(strict_types=1);

use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Proceeding\QueryBuilders\ProceedingQueryBuilder;
use Src\Domain\Process\Models\Process;

beforeEach(function (): void {
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

    $this->proceeding1 = Proceeding::factory()->create([
        'process_id' => $this->process1->id,
        'proceeding_date' => now()->subDays(3),
    ]);

    $this->proceeding2 = Proceeding::factory()->create([
        'process_id' => $this->process1->id,
        'proceeding_date' => now()->subDays(1),
    ]);

    $this->proceeding3 = Proceeding::factory()->create([
        'process_id' => $this->process2->id,
        'proceeding_date' => now(),
    ]);
});

it('filters proceedings by process ID when using forProcess', function (): void {
    $proceedings = Proceeding::query()
        ->forProcess($this->process1->id)
        ->get();

    expect($proceedings)->toHaveCount(2)
        ->and($proceedings->pluck('id'))->toContain($this->proceeding1->id)
        ->and($proceedings->pluck('id'))->toContain($this->proceeding2->id)
        ->and($proceedings->pluck('id'))->not->toContain($this->proceeding3->id);
});

it('includes process relationship when using withProcess', function (): void {
    $proceeding = Proceeding::query()
        ->where('id', $this->proceeding1->id)
        ->withProcess()
        ->first();

    expect($proceeding)->not->toBeNull()
        ->and($proceeding->relationLoaded('process'))->toBeTrue()
        ->and($proceeding->process)->not->toBeNull()
        ->and($proceeding->process->id)->toBe($this->process1->id);
});

it('orders proceedings by proceeding_date when using orderedByProceedingDate', function (): void {
    $proceedingIds = [$this->proceeding1->id, $this->proceeding2->id, $this->proceeding3->id];

    $proceedings = Proceeding::query()
        ->whereIn('id', $proceedingIds)
        ->orderedByProceedingDate()
        ->get();

    $proceedingDates = $proceedings->pluck('proceeding_date')->map(fn ($date) => $date->timestamp)->all();
    $sortedDates = $proceedingDates;
    rsort($sortedDates);

    expect($proceedingDates)->toBe($sortedDates)
        ->and($proceedings->first()->id)->toBe($this->proceeding3->id)
        ->and($proceedings->last()->id)->toBe($this->proceeding1->id);
});

it('can be chained with other query methods', function (): void {
    $proceedings = Proceeding::query()
        ->forProcess($this->process1->id)
        ->withProcess()
        ->orderedByProceedingDate()
        ->get();

    expect($proceedings)->toBeInstanceOf(Illuminate\Database\Eloquent\Collection::class)
        ->and($proceedings)->toHaveCount(2)
        ->and($proceedings->first()->relationLoaded('process'))->toBeTrue();
});

it('returns ProceedingQueryBuilder instance', function (): void {
    $queryBuilder = Proceeding::query();

    expect($queryBuilder)->toBeInstanceOf(ProceedingQueryBuilder::class);
});
