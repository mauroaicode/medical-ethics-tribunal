<?php

declare(strict_types=1);

namespace Src\Application\Admin\AuditLog\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\AuditLog\Models\AuditLog;

class AuditLogFinderService
{
    /**
     * Get all audit logs ordered by created_at
     *
     * @return Collection<int, AuditLog>
     */
    public function handle(): Collection
    {
        return AuditLog::query()
            ->with(['user', 'auditable'])
            ->orderedByCreatedAt()
            ->get();
    }
}
