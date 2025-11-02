<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Doctor\Controllers\DoctorController;

Route::middleware(['auth:sanctum'])->prefix('doctors')->group(function (): void {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/{doctor}', [DoctorController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('doctors')->group(function (): void {
    Route::post('/', [DoctorController::class, 'store']);
    Route::put('/{doctor}', [DoctorController::class, 'update']);
    Route::delete('/{doctor}', [DoctorController::class, 'destroy']);
});
