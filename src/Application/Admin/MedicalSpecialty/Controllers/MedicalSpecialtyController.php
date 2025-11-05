<?php

declare(strict_types=1);

namespace Src\Application\Admin\MedicalSpecialty\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\MedicalSpecialty\Resources\MedicalSpecialtyResource;
use Src\Application\Admin\MedicalSpecialty\Services\MedicalSpecialtyFinderService;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

class MedicalSpecialtyController
{
    /**
     * Display a listing of available medical specialties.
     */
    public function index(MedicalSpecialtyFinderService $medicalSpecialtyFinderService): Collection
    {
        return $medicalSpecialtyFinderService->handle()
            ->map(fn (MedicalSpecialty $specialty): array => MedicalSpecialtyResource::fromModel($specialty)->toArray());
    }
}
