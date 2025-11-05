<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Magistrate\Data\StoreMagistrateData;
use Src\Application\Admin\Magistrate\Data\UpdateMagistrateData;
use Src\Application\Admin\Magistrate\Resources\MagistrateResource;
use Src\Application\Admin\Magistrate\Services\MagistrateCreatorService;
use Src\Application\Admin\Magistrate\Services\MagistrateDeleterService;
use Src\Application\Admin\Magistrate\Services\MagistrateFinderService;
use Src\Application\Admin\Magistrate\Services\MagistrateUpdaterService;
use Src\Domain\Magistrate\Models\Magistrate;
use Throwable;

class MagistrateController
{
    /**
     * Display a listing of the resource.
     */
    public function index(MagistrateFinderService $magistrateFinderService): Collection
    {
        return $magistrateFinderService->handle()
            ->map(fn (Magistrate $magistrate): array => MagistrateResource::fromModel($magistrate)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Magistrate $magistrate): array
    {
        $magistrate->load('user');

        return MagistrateResource::fromModel($magistrate)->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(MagistrateCreatorService $magistrateCreatorService, StoreMagistrateData $storeMagistrateData): Response
    {
        $magistrate = $magistrateCreatorService->handle($storeMagistrateData);

        return response(MagistrateResource::fromModel($magistrate)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(
        MagistrateUpdaterService $magistrateUpdaterService,
        UpdateMagistrateData $updateMagistrateData,
        Magistrate $magistrate
    ): Response {
        $updatedMagistrate = $magistrateUpdaterService->handle($updateMagistrateData, $magistrate);

        return response(MagistrateResource::fromModel($updatedMagistrate)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(MagistrateDeleterService $magistrateDeleterService, Magistrate $magistrate): Response
    {
        $magistrateDeleterService->handle($magistrate);

        return new Response(status: 204);
    }
}
