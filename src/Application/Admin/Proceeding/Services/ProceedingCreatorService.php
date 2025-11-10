<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Services;

use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Src\Application\Admin\Proceeding\Data\StoreProceedingData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Shared\Enums\FileType;
use Throwable;

class ProceedingCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new proceeding with an associated PDF file
     *
     * @throws Throwable
     */
    public function handle(StoreProceedingData $storeProceedingData): Proceeding
    {
        return DB::transaction(function () use ($storeProceedingData) {
            $proceeding = Proceeding::query()->create([
                'process_id' => $storeProceedingData->process_id,
                'name' => $storeProceedingData->name,
                'description' => $storeProceedingData->description,
                'proceeding_date' => $storeProceedingData->proceeding_date,
            ]);

            $this->addDocumentToProceeding($proceeding, $storeProceedingData);

            $this->logAudit(
                action: 'create',
                model: $proceeding,
                oldValues: null,
                newValues: $proceeding->getAttributes(),
            );

            return $proceeding->load('process');
        });
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function addDocumentToProceeding(Proceeding $proceeding, StoreProceedingData $storeProceedingData): void
    {
        $proceeding->addMedia($storeProceedingData->file->getPathname())
            ->usingName($storeProceedingData->file->getClientOriginalName())
            ->usingFileName($storeProceedingData->file->getClientOriginalName())
            ->toMediaCollection(FileType::PROCEEDING_DOCUMENT->value);
    }
}
