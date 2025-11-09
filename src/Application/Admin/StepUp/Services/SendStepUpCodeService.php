<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Random\RandomException;
use Src\Application\Admin\StepUp\Notifications\StepUpCodeNotification;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Application\Shared\Traits\StepUpTrait;
use Src\Domain\User\Models\User;
use Throwable;

class SendStepUpCodeService
{
    use LogsAuditTrait;
    use StepUpTrait;

    /**
     * Send OTP code to user's email
     *
     * @throws Throwable
     */
    public function handle(User $user, string $action): string
    {
        return DB::transaction(function () use ($user, $action): string {
            $this->checkIfBlocked($user, $action);

            $code = $this->generateCode();
            $validityMinutes = config()->integer('step-up.code.validity_minutes');

            $cacheKey = $this->getCodeCacheKey($user->id, $action);
            Cache::put($cacheKey, $code, now()->addMinutes($validityMinutes));

            $user->notify(new StepUpCodeNotification($code, $action));

            $this->logStepUpAction($user, 'send_code', $action, [
                'code_sent' => true,
                'validity_minutes' => $validityMinutes,
            ]);

            return $code;
        });
    }

    /**
     * Generate OTP code
     *
     * @throws RandomException
     */
    private function generateCode(): string
    {
        $length = config()->integer('step-up.code.length');

        return str_pad((string) random_int(0, (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);
    }
}
