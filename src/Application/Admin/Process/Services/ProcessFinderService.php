<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Src\Application\Admin\Process\Data\ProcessFilterData;
use Src\Domain\Process\Models\Process;

class ProcessFinderService
{
    /**
     * Get paginated processes with relations and filters
     *
     * @param  ProcessFilterData  $filters  The filtering criteria.
     * @param  int  $perPage  Number of items per page.
     */
    public function handle(ProcessFilterData $filters, int $perPage = 50): LengthAwarePaginator
    {
        return Process::query()
            ->filters($filters)
            ->withRelations()
            ->withCount('proceedings')
            ->withoutTrashed()
            ->orderedByStartDate()
            ->paginate($perPage)
            ->appends(request()->query());
    }
}
