<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Complainant\Data\StoreComplainantData;
use Src\Application\Admin\Complainant\Data\UpdateComplainantData;
use Src\Application\Admin\Complainant\Resources\ComplainantIndexResource;
use Src\Application\Admin\Complainant\Resources\ComplainantResource;
use Src\Application\Admin\Complainant\Services\ComplainantCreatorService;
use Src\Application\Admin\Complainant\Services\ComplainantDeleterService;
use Src\Application\Admin\Complainant\Services\ComplainantFinderService;
use Src\Application\Admin\Complainant\Services\ComplainantUpdaterService;
use Src\Domain\Complainant\Models\Complainant;
use Throwable;

class ComplainantController
{
    /**
     * Display a listing of the resource.
     */
    public function index(ComplainantFinderService $complainantFinderService): Collection
    {
        return $complainantFinderService->handle()
            ->map(fn (Complainant $complainant): array => ComplainantIndexResource::fromModel($complainant)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Complainant $complainant): array
    {
        $complainant->load(['user', 'city']);

        return ComplainantResource::fromModel($complainant)->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(ComplainantCreatorService $complainantCreatorService, StoreComplainantData $storeComplainantData): Response
    {
        $complainant = $complainantCreatorService->handle($storeComplainantData);

        return response(ComplainantResource::fromModel($complainant)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(
        ComplainantUpdaterService $complainantUpdaterService,
        UpdateComplainantData $updateComplainantData,
        Complainant $complainant
    ): Response {
        $updatedComplainant = $complainantUpdaterService->handle($updateComplainantData, $complainant);

        return response(ComplainantResource::fromModel($updatedComplainant)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(ComplainantDeleterService $complainantDeleterService, Complainant $complainant): Response
    {
        $complainantDeleterService->handle($complainant);

        return new Response(status: 204);
    }
}
