<?php

declare(strict_types=1);

namespace Src\Application\Admin\AuditLog\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\AuditLog\Models\AuditLog;

class AuditLogResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public ?array $user,
        public string $action,
        public string $auditable_type,
        public int $auditable_id,
        public ?array $auditable,
        public ?array $old_values,
        public ?array $new_values,
        public string $ip_address,
        public ?string $user_agent,
        public ?string $location,
        public string $created_at,
    ) {}

    public static function fromModel(AuditLog $auditLog): self
    {
        return new self(
            id: $auditLog->id,
            user_id: $auditLog->user_id,
            user: $auditLog->user ? [
                'id' => $auditLog->user->id,
                'name' => $auditLog->user->name,
                'last_name' => $auditLog->user->last_name,
                'email' => $auditLog->user->email,
            ] : null,
            action: $auditLog->action,
            auditable_type: $auditLog->auditable_type,
            auditable_id: $auditLog->auditable_id,
            auditable: $auditLog->auditable ? [
                'id' => $auditLog->auditable->getKey(),
                'type' => class_basename($auditLog->auditable_type),
            ] : null,
            old_values: $auditLog->old_values,
            new_values: $auditLog->new_values,
            ip_address: $auditLog->ip_address,
            user_agent: $auditLog->user_agent,
            location: $auditLog->location,
            created_at: $auditLog->created_at->format('Y-m-d H:i:s'),
        );
    }
}
