<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Admin\Proceeding\Data\UpdateProceedingData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Shared\Enums\FileType;
use Throwable;

class ProceedingUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a proceeding and optionally add a new PDF file
     *
     * @throws Throwable
     */
    public function handle(UpdateProceedingData $updateProceedingData, Proceeding $proceeding): Proceeding
    {
        return DB::transaction(function () use ($updateProceedingData, $proceeding) {

            $oldValues = $proceeding->getAttributes();

            $updateData = array_filter([
                'name' => $updateProceedingData->name,
                'description' => $updateProceedingData->description,
                'proceeding_date' => $updateProceedingData->proceeding_date,
            ], fn (mixed $value): bool => ! is_null($value) && $value !== '');

            if ($updateData !== []) {
                $proceeding->update($updateData);
            }

            if (! is_null($updateProceedingData->file)) {
                $this->updateDocumentInProceeding($proceeding, $updateProceedingData);
            }

            $updatedProceeding = $proceeding->fresh('process');

            $this->logAudit(
                action: 'update',
                model: $updatedProceeding,
                oldValues: $oldValues,
                newValues: $updatedProceeding->getAttributes(),
            );

            return $updatedProceeding;
        });
    }

    /**
     * Update the document file for a proceeding
     * Clears existing media and adds the new file
     */
    private function updateDocumentInProceeding(Proceeding $proceeding, UpdateProceedingData $updateProceedingData): void
    {
        $proceeding->clearMediaCollection(FileType::PROCEEDING_DOCUMENT->value);
        $proceeding->addMedia($updateProceedingData->file->getPathname())
            ->usingName($updateProceedingData->file->getClientOriginalName())
            ->usingFileName($updateProceedingData->file->getClientOriginalName())
            ->toMediaCollection(FileType::PROCEEDING_DOCUMENT->value);
    }
}
