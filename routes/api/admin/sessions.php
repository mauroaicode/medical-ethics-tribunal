<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Session\Controllers\SessionController;

Route::middleware(['auth:sanctum', 'super_admin'])->prefix('sessions')->group(function (): void {
    Route::get('/', [SessionController::class, 'index']);
});
