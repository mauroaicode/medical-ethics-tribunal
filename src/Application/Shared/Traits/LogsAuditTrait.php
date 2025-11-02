<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Src\Application\Shared\Services\LocationService;
use Src\Domain\AuditLog\Models\AuditLog;

trait LogsAuditTrait
{
    /**
     * Log an audit entry for operations
     */
    protected function logAudit(
        string $action,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): void {
        $authUser = Auth::user();

        if (! $authUser) {
            return;
        }

        $ipAddress = request()->ip();
        $locationService = new LocationService;
        $location = $locationService->getLocationFromIp($ipAddress);

        AuditLog::query()->create([
            'user_id' => $authUser->id,
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'location' => $location,
            'created_at' => now(),
        ]);
    }
}
