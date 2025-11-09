<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use Src\Application\Admin\StepUp\Exceptions\UserBlockedException;
use Src\Application\Shared\Services\LocationService;
use Src\Domain\AuditLog\Models\AuditLog;
use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;

trait StepUpTrait
{
    /**
     * Check if user is blocked for the given action
     *
     * @throws UserBlockedException
     */
    protected function checkIfBlocked(User $user, string $action): void
    {
        $activeBlock = SessionBlock::query()
            ->activeForUserAndAction($user->id, $action);

        if ($activeBlock) {
            $this->logStepUpAction($user, 'blocked_access_attempt', $action, [
                'blocked_until' => $activeBlock->blocked_until->toIso8601String(),
            ]);

            throw new UserBlockedException($activeBlock);
        }
    }

    /**
     * Get a cache key for OTP code
     */
    protected function getCodeCacheKey(int $userId, string $action): string
    {
        $prefix = config('step-up.code.cache_key_prefix');

        return "{$prefix}_{$userId}_{$action}";
    }

    /**
     * Log step-up action to audit log
     */
    protected function logStepUpAction(User $user, string $action, string $contextAction, array $data = []): void
    {
        $ipAddress = request()->ip();
        $locationService = new LocationService;
        $location = $locationService->getLocationFromIp($ipAddress);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => array_merge([
                'context_action' => $contextAction,
            ], $data),
            'ip_address' => $ipAddress,
            'user_agent' => request()->userAgent(),
            'location' => $location,
            'created_at' => now(),
        ]);
    }
}
