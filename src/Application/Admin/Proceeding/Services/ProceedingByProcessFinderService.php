<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Models\Process;

class ProceedingByProcessFinderService
{
    /**
     * Get all proceedings for a specific process
     *
     * @return Collection<int, Proceeding>
     */
    public function handle(Process $process): Collection
    {
        return Proceeding::query()
            ->forProcess($process->id)
            ->withProcess()
            ->orderedByProceedingDate()
            ->get();
    }
}
