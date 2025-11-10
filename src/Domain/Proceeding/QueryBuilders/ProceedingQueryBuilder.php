<?php

declare(strict_types=1);

namespace Src\Domain\Proceeding\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Proceeding\Models\Proceeding;

/** @extends Builder<Proceeding> */
class ProceedingQueryBuilder extends Builder
{
    /**
     * Filter proceedings by process ID
     */
    public function forProcess(int $processId): self
    {
        return $this->where('process_id', $processId);
    }

    /**
     * Include process relationship
     */
    public function withProcess(): self
    {
        return $this->with('process');
    }

    /**
     * Order proceedings by proceeding_date (most recent first)
     */
    public function orderedByProceedingDate(): self
    {
        return $this->latest('proceeding_date')->latest();
    }
}
