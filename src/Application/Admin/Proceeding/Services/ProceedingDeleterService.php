<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Proceeding\Models\Proceeding;
use Throwable;

class ProceedingDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a proceeding and its associated media files
     *
     * @throws Throwable
     */
    public function handle(Proceeding $proceeding): void
    {
        DB::transaction(function () use ($proceeding): void {
            $oldValues = $proceeding->getAttributes();

            $proceeding->clearMediaCollection($proceeding->getMediaCollectionName());

            $proceeding->delete();

            $this->logAudit(
                action: 'delete',
                model: $proceeding,
                oldValues: $oldValues,
            );
        });
    }
}
