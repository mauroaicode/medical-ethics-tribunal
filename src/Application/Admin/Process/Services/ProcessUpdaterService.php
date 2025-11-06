<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Admin\Process\Data\UpdateProcessData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Process\Models\Process;
use Throwable;

class ProcessUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a process
     *
     * @throws Throwable
     */
    public function handle(UpdateProcessData $updateProcessData, Process $process): Process
    {
        return DB::transaction(function () use ($updateProcessData, $process) {
            $oldValues = $process->getAttributes();

            $updateData = array_filter([
                'complainant_id' => $updateProcessData->complainant_id,
                'doctor_id' => $updateProcessData->doctor_id,
                'magistrate_instructor_id' => $updateProcessData->magistrate_instructor_id,
                'magistrate_ponente_id' => $updateProcessData->magistrate_ponente_id,
                'name' => $updateProcessData->name,
                'start_date' => $updateProcessData->start_date,
                'status' => $updateProcessData->status,
                'description' => $updateProcessData->description,
            ], fn (mixed $value): bool => ! is_null($value));

            if ($updateData !== []) {
                $process->update($updateData);
            }

            $updatedProcess = $process->fresh([
                'complainant.user',
                'complainant.city',
                'doctor.user',
                'doctor.specialty',
                'magistrateInstructor.user',
                'magistratePonente.user',
                'templateDocuments.media',
                'templateDocuments.template',
            ]);

            $this->logAudit(
                action: 'update',
                model: $updatedProcess,
                oldValues: $oldValues,
                newValues: $updatedProcess->getAttributes(),
            );

            return $updatedProcess;
        });
    }
}
