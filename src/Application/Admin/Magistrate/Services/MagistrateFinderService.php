<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Magistrate\Models\Magistrate;

class MagistrateFinderService
{
    /**
     * Get all magistrates with relations
     *
     * @return Collection<int, Magistrate>
     */
    public function handle(): Collection
    {
        return Magistrate::query()
            ->withRelations()
            ->withoutTrashed()
            ->orderedByCreatedAt()
            ->get();
    }
}
