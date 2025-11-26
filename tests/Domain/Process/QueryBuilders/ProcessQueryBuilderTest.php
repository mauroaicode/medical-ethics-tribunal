<?php

declare(strict_types=1);

use Src\Application\Admin\Process\Data\ProcessFilterData;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\Process\Enums\ProcessStatus;
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

it('orders processes by start_date correctly (most recent first)', function (): void {
    $this->process1->update(['start_date' => '2025-01-15']);
    $this->process2->update(['start_date' => '2025-02-10']);

    $processIds = [$this->process1->id, $this->process2->id];

    $processes = Process::query()
        ->whereIn('id', $processIds)
        ->orderedByStartDate()
        ->get();

    $startDates = $processes->pluck('start_date')->map(fn ($date) => $date->timestamp)->all();
    $sortedStartDates = $startDates;
    rsort($sortedStartDates);

    expect($startDates)->toBe($sortedStartDates)
        ->and($processes->first()->id)->toBe($this->process2->id) // Más reciente primero
        ->and($processes->last()->id)->toBe($this->process1->id);
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

it('filters processes by process_number', function (): void {
    $filterData = ProcessFilterData::from([
        'process_number' => $this->process1->process_number,
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(1)
        ->and($processes->first()->id)->toBe($this->process1->id);
});

it('filters processes by complainant_document_number', function (): void {
    $filterData = ProcessFilterData::from([
        'complainant_document_number' => $this->user1->document_number,
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(2)
        ->and($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id);
});

it('filters processes by doctor_name', function (): void {
    $filterData = ProcessFilterData::from([
        'doctor_name' => $this->user2->name,
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(2)
        ->and($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id);
});

it('filters processes by doctor_name with last_name', function (): void {
    $filterData = ProcessFilterData::from([
        'doctor_name' => $this->user2->last_name,
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(2)
        ->and($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id);
});

it('filters processes by start_date', function (): void {
    $filterData = ProcessFilterData::from([
        'start_date' => $this->process1->start_date->format('Y-m-d'),
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    $processIds = $processes->pluck('id')->all();
    expect($processIds)->toContain($this->process1->id);
});

it('filters processes by date range (start_date_from and start_date_to)', function (): void {
    $this->process1->update(['start_date' => '2025-01-05']);
    $this->process2->update(['start_date' => '2025-02-10']);

    $filterData = ProcessFilterData::from([
        'start_date_from' => '2025-01-02',
        'start_date_to' => '2025-02-12',
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id);
});

it('filters processes by date range from (start_date_from only)', function (): void {
    $this->process1->update(['start_date' => '2025-01-05']);
    $this->process2->update(['start_date' => '2024-12-20']);

    $filterData = ProcessFilterData::from([
        'start_date_from' => '2025-01-02',
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->not->toContain($this->process2->id);
});

it('filters processes by date range to (start_date_to only)', function (): void {
    $this->process1->update(['start_date' => '2025-01-05']);
    $this->process2->update(['start_date' => '2025-02-15']);

    $filterData = ProcessFilterData::from([
        'start_date_to' => '2025-02-12',
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->not->toContain($this->process2->id);
});

it('filters processes by status', function (): void {
    $this->process1->update(['status' => ProcessStatus::IN_PROGRESS]);
    $this->process2->update(['status' => ProcessStatus::DRAFT]);

    $filterData = ProcessFilterData::from([
        'status' => ProcessStatus::IN_PROGRESS->value,
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(1)
        ->and($processes->first()->id)->toBe($this->process1->id)
        ->and($processes->first()->status)->toBe(ProcessStatus::IN_PROGRESS);
});

it('applies multiple filters simultaneously', function (): void {
    $filterData = ProcessFilterData::from([
        'process_number' => $this->process1->process_number,
        'start_date' => $this->process1->start_date->format('Y-m-d'),
    ]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes)->toHaveCount(1)
        ->and($processes->first()->id)->toBe($this->process1->id);
});

it('returns all processes when no filters are applied', function (): void {
    $filterData = ProcessFilterData::from([]);

    $processes = Process::query()
        ->filters($filterData)
        ->get();

    expect($processes->pluck('id'))->toContain($this->process1->id)
        ->and($processes->pluck('id'))->toContain($this->process2->id);
});
