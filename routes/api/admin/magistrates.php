<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Magistrate\Controllers\MagistrateController;

Route::middleware(['auth:sanctum'])->prefix('magistrates')->group(function (): void {
    Route::get('/', [MagistrateController::class, 'index']);
    Route::get('/{magistrate}', [MagistrateController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('magistrates')->group(function (): void {
    Route::post('/', [MagistrateController::class, 'store']);
    Route::put('/{magistrate}', [MagistrateController::class, 'update']);
    Route::delete('/{magistrate}', [MagistrateController::class, 'destroy']);
});
