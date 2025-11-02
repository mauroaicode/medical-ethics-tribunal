<?php

declare(strict_types=1);

namespace Src\Domain\MedicalSpecialty\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

/** @extends Builder<MedicalSpecialty> */
class MedicalSpecialtyQueryBuilder extends Builder
{
    /**
     * Order specialties by name
     */
    public function orderedByName(): self
    {
        return $this->orderBy('name');
    }
}
