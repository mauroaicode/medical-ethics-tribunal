<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Complainant\Models\Complainant;

class ComplainantFinderService
{
    /**
     * Get all complainants with relations
     *
     * @return Collection<int, Complainant>
     */
    public function handle(): Collection
    {
        return Complainant::query()
            ->withRelations()
            ->withoutTrashed()
            ->orderedByCreatedAt()
            ->get();
    }
}
