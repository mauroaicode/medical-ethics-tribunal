<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Src\Application\Admin\StepUp\Exceptions\UserBlockedException;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Application\Shared\Traits\StepUpTrait;
use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;
use Throwable;

class VerifyStepUpCodeService
{
    use LogsAuditTrait;
    use StepUpTrait;

    /**
     * Verify OTP code
     *
     * @return array{valid: bool, remaining_attempts: int|null} Returns validation result and remaining attempts
     *
     * @throws Throwable
     */
    public function handle(User $user, string $action, string $code): array
    {
        $this->checkIfBlocked($user, $action);

        $sessionBlockToThrow = null;
        $remainingAttempts = null;

        $result = DB::transaction(function () use ($user, $action, $code, &$sessionBlockToThrow, &$remainingAttempts): bool {

            $cacheKey = $this->getCodeCacheKey($user->id, $action);
            $storedCode = Cache::get($cacheKey);

            if (! $storedCode || $storedCode !== $code) {
                $sessionBlock = $this->incrementFailedAttempts($user, $action);

                $this->logStepUpAction($user, 'verify_code_failed', $action, [
                    'code_valid' => false,
                ]);

                if (! is_null($sessionBlock)) {

                    Cache::forget($cacheKey);
                    $sessionBlockToThrow = $sessionBlock;
                    $remainingAttempts = 0;

                    return false;
                }

                $attemptsKey = $this->getAttemptsCacheKey($user->id, $action);
                $currentAttempts = Cache::get($attemptsKey, 0);
                $maxAttempts = config()->integer('step-up.attempts.max_attempts');
                $remainingAttempts = max(0, $maxAttempts - $currentAttempts);

                return false;
            }

            Cache::forget($cacheKey);
            $this->resetFailedAttempts($user, $action);

            $verificationKey = $this->getVerificationCacheKey($user->id, $action);
            $verificationValidityMinutes = config()->integer('step-up.verification.validity_minutes');
            Cache::put($verificationKey, true, now()->addMinutes($verificationValidityMinutes));

            $this->logStepUpAction($user, 'verify_code_success', $action, [
                'code_verified' => true,
            ]);

            return true;
        });

        if (! is_null($sessionBlockToThrow)) {
            throw new UserBlockedException($sessionBlockToThrow);
        }

        return [
            'valid' => $result,
            'remaining_attempts' => $remainingAttempts,
        ];
    }

    /**
     * Increment failed attempts and block if a threshold is reached
     *
     * @return SessionBlock|null Returns the SessionBlock if user was blocked, null otherwise
     *
     * @throws Throwable
     */
    private function incrementFailedAttempts(User $user, string $action): ?SessionBlock
    {
        $attemptsKey = $this->getAttemptsCacheKey($user->id, $action);
        $attempts = Cache::get($attemptsKey, 0);
        $attempts++;

        $maxAttempts = config()->integer('step-up.attempts.max_attempts');

        if ($attempts >= $maxAttempts) {
            $sessionBlock = $this->blockUser($user, $action);

            Cache::forget($attemptsKey);

            $this->logStepUpAction($user, 'user_blocked', $action, [
                'failed_attempts' => $attempts,
                'max_attempts' => $maxAttempts,
            ]);

            return $sessionBlock;
        }

        Cache::put($attemptsKey, $attempts, now()->addMinutes(config()->integer('step-up.attempts.duration_minutes')));

        return null;
    }

    /**
     * Reset failed attempts
     */
    private function resetFailedAttempts(User $user, string $action): void
    {
        $attemptsKey = $this->getAttemptsCacheKey($user->id, $action);
        Cache::forget($attemptsKey);
    }

    /**
     * Block user session
     *
     * @return SessionBlock The created session block
     *
     * @throws Throwable
     */
    private function blockUser(User $user, string $action): SessionBlock
    {
        $blockDurationMinutes = config()->integer('step-up.block.duration_minutes');
        $blockedUntil = now()->addMinutes($blockDurationMinutes);

        $sessionBlock = SessionBlock::query()->create([
            'user_id' => $user->id,
            'session_id' => request()->header('X-Session-ID'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => $action,
            'blocked_until' => $blockedUntil,
        ]);

        $user->tokens()->delete();

        return $sessionBlock;
    }

    /**
     * Get cache key for failed attempts
     */
    private function getAttemptsCacheKey(int $userId, string $action): string
    {
        $prefix = config('step-up.attempts.cache_key_prefix');

        return "{$prefix}_{$userId}_{$action}";
    }

    /**
     * Get a cache key for step-up verification
     */
    private function getVerificationCacheKey(int $userId, string $action): string
    {
        $prefix = config('step-up.verification.cache_key_prefix');

        return "{$prefix}_{$userId}_{$action}";
    }
}
