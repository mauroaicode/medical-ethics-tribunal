<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\MedicalSpecialty\Controllers\MedicalSpecialtyController;

Route::middleware(['auth:sanctum'])->prefix('medical-specialties')->group(function (): void {
    Route::get('/', [MedicalSpecialtyController::class, 'index']);
});
