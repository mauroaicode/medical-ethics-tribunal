<?php

declare(strict_types=1);

namespace Src\Domain\City\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\City\Models\City;

/** @extends Builder<City> */
class CityQueryBuilder extends Builder
{
    /**
     * Filter cities by department ID
     */
    public function byDepartment(int $departmentId): self
    {
        return $this->where('iddepartamento', $departmentId);
    }

    /**
     * Order cities by description
     */
    public function orderedByDescription(): self
    {
        return $this->orderBy('descripcion');
    }
}
