<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Complainant\Data\StoreComplainantData;
use Src\Application\Admin\Complainant\Data\UpdateComplainantData;
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
    public function index(): Collection
    {
        return (new ComplainantFinderService)->handle()
            ->map(fn (Complainant $complainant): array => ComplainantResource::fromModel($complainant)->toArray());
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
    public function store(StoreComplainantData $storeComplainantData): Response
    {
        $complainant = (new ComplainantCreatorService)->handle($storeComplainantData);

        return response(ComplainantResource::fromModel($complainant)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(UpdateComplainantData $updateComplainantData, Complainant $complainant): Response
    {
        $updatedComplainant = (new ComplainantUpdaterService)->handle($updateComplainantData, $complainant);

        return response(ComplainantResource::fromModel($updatedComplainant)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(Complainant $complainant): Response
    {
        (new ComplainantDeleterService)->handle($complainant);

        return new Response(status: 204);
    }
}
