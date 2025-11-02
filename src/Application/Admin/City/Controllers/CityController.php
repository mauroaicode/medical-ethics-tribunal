<?php

declare(strict_types=1);

namespace Src\Application\Admin\City\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\City\Resources\CityResource;
use Src\Application\Admin\City\Services\CityFinderService;
use Src\Domain\City\Models\City;

class CityController
{
    /**
     * Display a listing of available cities.
     */
    public function index(): Collection
    {
        return (new CityFinderService)->handle()
            ->map(fn (City $city): array => CityResource::fromModel($city)->toArray());
    }
}
