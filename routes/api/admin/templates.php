<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Template\Controllers\TemplateController;

Route::middleware(['auth:sanctum'])->prefix('templates')->group(function (): void {
    Route::get('/', [TemplateController::class, 'index']);
    Route::get('/{template}', [TemplateController::class, 'show']);
    Route::post('/sync', [TemplateController::class, 'sync']);
    Route::post('/assign-to-process', [TemplateController::class, 'assignToProcess']);
});
