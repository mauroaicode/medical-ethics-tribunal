<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Doctor\Models\Doctor;

class DoctorFinderService
{
    /**
     * Get all doctors with relations
     *
     * @return Collection<int, Doctor>
     */
    public function handle(): Collection
    {
        return Doctor::query()
            ->withRelations()
            ->withoutTrashed()
            ->orderedByCreatedAt()
            ->get();
    }
}
