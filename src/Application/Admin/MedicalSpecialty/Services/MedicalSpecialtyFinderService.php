<?php

declare(strict_types=1);

namespace Src\Application\Admin\MedicalSpecialty\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

class MedicalSpecialtyFinderService
{
    /**
     * Get all medical specialties ordered by name
     *
     * @return Collection<int, MedicalSpecialty>
     */
    public function handle(): Collection
    {
        return MedicalSpecialty::query()
            ->orderedByName()
            ->get();
    }
}
