<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\City\Controllers\CityController;

Route::middleware(['auth:sanctum'])->prefix('cities')->group(function (): void {
    Route::get('/', [CityController::class, 'index']);
});
