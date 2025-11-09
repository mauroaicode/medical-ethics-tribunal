<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;
use Src\Domain\SessionBlock\Models\SessionBlock;

class UserBlockedException extends RuntimeException
{
    public function __construct(
        private readonly SessionBlock $sessionBlock
    ) {
        $minutesRemaining = (int) ceil(now()->diffInMinutes($sessionBlock->blocked_until));
        $message = __('step_up.blocked', ['minutes' => $minutesRemaining]);

        parent::__construct($message);
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(): JsonResponse
    {
        // Calculate remaining time (always recalculated for current time)
        $secondsRemaining = max(0, now()->diffInSeconds($this->sessionBlock->blocked_until));
        $minutesRemaining = (int) ceil($secondsRemaining / 60);

        return response()->json([
            'message' => __('step_up.blocked', ['minutes' => $minutesRemaining]),
            'code' => 403,
            'blocked' => true,
            'blocked_until' => $this->sessionBlock->blocked_until->toIso8601String(),
            'minutes_remaining' => $minutesRemaining,
            'seconds_remaining' => (int) $secondsRemaining,
            'action' => $this->sessionBlock->action,
        ], 403);
    }
}
