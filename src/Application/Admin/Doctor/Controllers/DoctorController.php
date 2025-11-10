<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Doctor\Data\StoreDoctorData;
use Src\Application\Admin\Doctor\Data\UpdateDoctorData;
use Src\Application\Admin\Doctor\Resources\DoctorIndexResource;
use Src\Application\Admin\Doctor\Resources\DoctorResource;
use Src\Application\Admin\Doctor\Services\DoctorCreatorService;
use Src\Application\Admin\Doctor\Services\DoctorDeleterService;
use Src\Application\Admin\Doctor\Services\DoctorFinderService;
use Src\Application\Admin\Doctor\Services\DoctorUpdaterService;
use Src\Domain\Doctor\Models\Doctor;
use Throwable;

class DoctorController
{
    /**
     * Display a listing of the resource.
     */
    public function index(DoctorFinderService $doctorFinderService): Collection
    {
        return $doctorFinderService->handle()
            ->map(fn (Doctor $doctor): array => DoctorIndexResource::fromModel($doctor)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor): array
    {
        $doctor->load(['user', 'specialty']);

        return DoctorResource::fromModel($doctor)->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(DoctorCreatorService $doctorCreatorService, StoreDoctorData $storeDoctorData): Response
    {
        $doctor = $doctorCreatorService->handle($storeDoctorData);

        return response(DoctorResource::fromModel($doctor)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(
        DoctorUpdaterService $doctorUpdaterService,
        UpdateDoctorData $updateDoctorData,
        Doctor $doctor
    ): Response {
        $updatedDoctor = $doctorUpdaterService->handle($updateDoctorData, $doctor);

        return response(DoctorResource::fromModel($updatedDoctor)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(Doctor $doctor): Response
    {
        (new DoctorDeleterService)->handle($doctor);

        return new Response(status: 204);
    }
}
