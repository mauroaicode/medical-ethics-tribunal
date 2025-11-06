<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Process\Models\Process;
use Throwable;

class ProcessDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a process (soft delete)
     *
     * @throws Throwable
     */
    public function handle(Process $process, string $deletedReason): Process
    {
        return DB::transaction(function () use ($process, $deletedReason): Process {
            $oldValues = $process->getAttributes();

            $process->update([
                'deleted_reason' => $deletedReason,
            ]);

            $process->delete();

            $this->logAudit(
                action: 'delete',
                model: $process,
                oldValues: $oldValues,
            );

            return $process;
        });
    }
}
