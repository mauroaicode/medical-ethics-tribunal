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
    Route::put('/{process}', [ProcessController::class, 'update']);
    Route::delete('/{process}', [ProcessController::class, 'destroy']);
});
