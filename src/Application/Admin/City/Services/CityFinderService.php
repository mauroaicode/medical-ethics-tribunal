<?php

declare(strict_types=1);

namespace Src\Application\Admin\City\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\City\Models\City;
use Src\Domain\Department\Models\Department;

class CityFinderService
{
    /**
     * Get all cities for the default department
     *
     * @return Collection<int, City>
     */
    public function handle(): Collection
    {
        $defaultDepartmentName = config('app.default_department');

        $department = Department::query()
            ->where('descripcion', $defaultDepartmentName)
            ->first();

        if (! $department) {
            return City::query()->whereRaw('1 = 0')->get();
        }

        return City::query()
            ->byDepartment($department->id)
            ->orderedByDescription()
            ->get();
    }
}
