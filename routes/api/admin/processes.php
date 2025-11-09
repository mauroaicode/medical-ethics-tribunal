<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Process\Controllers\ProcessController;

Route::middleware(['auth:sanctum'])->prefix('processes')->group(function (): void {
    Route::get('/', [ProcessController::class, 'index']);
    Route::get('/{process}', [ProcessController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('processes')->group(function (): void {
    Route::post('/', [ProcessController::class, 'store']);
    Route::middleware(['step_up:process.update'])->group(function (): void {
        Route::put('/{process}', [ProcessController::class, 'update']);
    });
    Route::middleware(['step_up:process.delete'])->group(function (): void {
        Route::delete('/{process}', [ProcessController::class, 'destroy']);
    });
});
