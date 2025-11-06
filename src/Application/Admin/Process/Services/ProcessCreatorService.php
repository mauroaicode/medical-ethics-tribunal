<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Application\Admin\Process\Data\StoreProcessData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Process\Enums\ProcessStatus;
use Src\Domain\Process\Models\Process;
use Throwable;

class ProcessCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new process
     *
     * @throws Throwable
     */
    public function handle(StoreProcessData $storeProcessData): Process
    {
        return DB::transaction(function () use ($storeProcessData) {
            $process = Process::query()->create([
                'complainant_id' => $storeProcessData->complainant_id,
                'doctor_id' => $storeProcessData->doctor_id,
                'magistrate_instructor_id' => $storeProcessData->magistrate_instructor_id,
                'magistrate_ponente_id' => $storeProcessData->magistrate_ponente_id,
                'name' => $storeProcessData->name,
                'process_number' => $this->generateProcessNumber(),
                'start_date' => $storeProcessData->start_date,
                'status' => ProcessStatus::DRAFT,
                'description' => $storeProcessData->description,
            ]);

            $this->logAudit(
                action: 'create',
                model: $process,
                oldValues: null,
                newValues: $process->getAttributes(),
            );

            return $process->load([
                'complainant.user',
                'complainant.city',
                'doctor.user',
                'doctor.specialty',
                'magistrateInstructor.user',
                'magistratePonente.user',
                'templateDocuments.media',
                'templateDocuments.template',
            ]);
        });
    }

    /**
     * Generate consecutive process number
     */
    private function generateProcessNumber(): string
    {
        $lastProcess = Process::withTrashed()->orderBy('id', 'desc')->first();
        $nextId = $lastProcess ? $lastProcess->id + 1 : 1;

        // Format: PRO-0001, PRO-0002, etc.
        return 'PRO-'.Str::padLeft((string) $nextId, 4, '0');
    }
}
