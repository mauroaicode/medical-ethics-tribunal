<?php

declare(strict_types=1);

namespace Src\Domain\AuditLog\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\AuditLog\Models\AuditLog;

/** @extends Builder<AuditLog> */
class AuditLogQueryBuilder extends Builder
{
    /**
     * Filter audit logs by user ID
     */
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Filter audit logs by action
     */
    public function forAction(string $action): self
    {
        return $this->where('action', $action);
    }

    /**
     * Filter audit logs by auditable type
     */
    public function forAuditableType(string $auditableType): self
    {
        return $this->where('auditable_type', $auditableType);
    }

    /**
     * Order audit logs by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest();
    }
}
