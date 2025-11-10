<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Proceeding\Controllers\ProceedingController;

Route::middleware(['auth:sanctum'])->prefix('proceedings')->group(function (): void {
    Route::get('/process/{process}', [ProceedingController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('proceedings')->group(function (): void {
    Route::post('/', [ProceedingController::class, 'store']);
    Route::put('/{proceeding}', [ProceedingController::class, 'update']);
    Route::delete('/{proceeding}', [ProceedingController::class, 'destroy']);
});
