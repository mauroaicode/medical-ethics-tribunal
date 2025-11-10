<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Proceeding\Data\StoreProceedingData;
use Src\Application\Admin\Proceeding\Data\UpdateProceedingData;
use Src\Application\Admin\Proceeding\Resources\ProceedingResource;
use Src\Application\Admin\Proceeding\Services\ProceedingByProcessFinderService;
use Src\Application\Admin\Proceeding\Services\ProceedingCreatorService;
use Src\Application\Admin\Proceeding\Services\ProceedingDeleterService;
use Src\Application\Admin\Proceeding\Services\ProceedingUpdaterService;
use Src\Domain\Proceeding\Models\Proceeding;
use Src\Domain\Process\Models\Process;
use Throwable;

class ProceedingController
{
    /**
     * Display a listing of proceedings for a specific process.
     */
    public function index(ProceedingByProcessFinderService $proceedingByProcessFinderService, Process $process): Collection
    {
        return $proceedingByProcessFinderService->handle($process)
            ->map(fn (Proceeding $proceeding): array => ProceedingResource::fromModel($proceeding)->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(ProceedingCreatorService $proceedingCreatorService, StoreProceedingData $storeProceedingData): Response
    {
        $proceeding = $proceedingCreatorService->handle($storeProceedingData);

        return response(ProceedingResource::fromModel($proceeding)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(
        ProceedingUpdaterService $proceedingUpdaterService,
        UpdateProceedingData $updateProceedingData,
        Proceeding $proceeding
    ): Response {
        $updatedProceeding = $proceedingUpdaterService->handle($updateProceedingData, $proceeding);

        return response(ProceedingResource::fromModel($updatedProceeding)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(ProceedingDeleterService $proceedingDeleterService, Proceeding $proceeding): Response
    {
        $proceedingDeleterService->handle($proceeding);

        return new Response(status: 204);
    }
}
