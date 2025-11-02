<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Complainant\Controllers\ComplainantController;

Route::middleware(['auth:sanctum'])->prefix('complainants')->group(function (): void {
    Route::get('/', [ComplainantController::class, 'index']);
    Route::get('/{complainant}', [ComplainantController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('complainants')->group(function (): void {
    Route::post('/', [ComplainantController::class, 'store']);
    Route::put('/{complainant}', [ComplainantController::class, 'update']);
    Route::delete('/{complainant}', [ComplainantController::class, 'destroy']);
});
