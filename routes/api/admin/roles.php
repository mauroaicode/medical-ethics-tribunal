<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Role\Controllers\RoleController;

Route::middleware(['auth:sanctum', 'admin'])->prefix('roles')->group(function (): void {
    Route::get('/', [RoleController::class, 'index']);
});
