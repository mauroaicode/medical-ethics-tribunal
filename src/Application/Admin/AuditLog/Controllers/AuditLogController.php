<?php

declare(strict_types=1);

namespace Src\Application\Admin\AuditLog\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\AuditLog\Resources\AuditLogResource;
use Src\Application\Admin\AuditLog\Services\AuditLogFinderService;
use Src\Domain\AuditLog\Models\AuditLog;

class AuditLogController
{
    /**
     * Display a listing of audit logs.
     */
    public function index(AuditLogFinderService $auditLogFinderService): Collection
    {
        return $auditLogFinderService->handle()
            ->map(fn (AuditLog $auditLog): array => AuditLogResource::fromModel($auditLog)->toArray());
    }
}
