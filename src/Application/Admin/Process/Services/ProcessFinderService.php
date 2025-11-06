<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Process\Models\Process;

class ProcessFinderService
{
    /**
     * Get all processes with relations
     *
     * @return Collection<int, Process>
     */
    public function handle(): Collection
    {
        return Process::query()
            ->withRelations()
            ->withoutTrashed()
            ->orderedByCreatedAt()
            ->get();
    }
}
