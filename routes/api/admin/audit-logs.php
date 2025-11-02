<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\AuditLog\Controllers\AuditLogController;

Route::middleware(['auth:sanctum', 'admin'])->prefix('audit-logs')->group(function (): void {
    Route::get('/', [AuditLogController::class, 'index']);
});
