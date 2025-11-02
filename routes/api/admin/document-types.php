<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\DocumentType\Controllers\DocumentTypeController;

Route::middleware(['auth:sanctum'])->prefix('document-types')->group(function (): void {
    Route::get('/', [DocumentTypeController::class, 'index']);
});
