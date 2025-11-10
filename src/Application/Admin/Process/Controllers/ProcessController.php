<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Process\Data\DeleteProcessData;
use Src\Application\Admin\Process\Data\StoreProcessData;
use Src\Application\Admin\Process\Data\UpdateProcessData;
use Src\Application\Admin\Process\Resources\ProcessResource;
use Src\Application\Admin\Process\Services\ProcessCreatorService;
use Src\Application\Admin\Process\Services\ProcessDeleterService;
use Src\Application\Admin\Process\Services\ProcessFinderService;
use Src\Application\Admin\Process\Services\ProcessUpdaterService;
use Src\Domain\Process\Models\Process;
use Throwable;

class ProcessController
{
    /**
     * Display a listing of the resource.
     */
    public function index(ProcessFinderService $processFinderService): Collection
    {
        return $processFinderService->handle()
            ->map(fn (Process $process): array => ProcessResource::fromModel($process)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(Process $process): array
    {
        $process->load([
            'complainant.user',
            'complainant.city',
            'doctor.user',
            'doctor.specialty',
            'magistrateInstructor.user',
            'magistratePonente.user',
            'templateDocuments.media',
            'templateDocuments.template',
            'proceedings' => function ($query): void {
                $query->latest('proceeding_date')->latest();
            },
        ]);

        return ProcessResource::fromModel($process)->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(ProcessCreatorService $processCreatorService, StoreProcessData $storeProcessData): Response
    {
        $process = $processCreatorService->handle($storeProcessData);

        return response(ProcessResource::fromModel($process)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(
        ProcessUpdaterService $processUpdaterService,
        UpdateProcessData $updateProcessData,
        Process $process
    ): Response {
        $updatedProcess = $processUpdaterService->handle($updateProcessData, $process);

        return response(ProcessResource::fromModel($updatedProcess)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(
        ProcessDeleterService $processDeleterService,
        DeleteProcessData $deleteProcessData,
        Process $process
    ): Response {
        $processDeleterService->handle($process, $deleteProcessData->deleted_reason);

        return new Response(status: 204);
    }
}
