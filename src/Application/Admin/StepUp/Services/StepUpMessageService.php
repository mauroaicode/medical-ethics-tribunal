<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Services;

class StepUpMessageService
{
    /**
     * Build an error message for invalid code with remaining attempts information
     *
     * @param  int|null  $remainingAttempts  Number of remaining attempts (null if not applicable)
     * @return array{message: string, remaining_attempts: int|null}
     */
    public function handle(?int $remainingAttempts): array
    {
        $message = __('step_up.code_invalid');

        if ($remainingAttempts !== null && $remainingAttempts > 0) {
            $attemptsKey = $remainingAttempts === 1
                ? 'step_up.attempts_remaining_singular'
                : 'step_up.attempts_remaining_plural';

            $attemptsText = __($attemptsKey, ['count' => $remainingAttempts]);

            $message = __('step_up.code_invalid_with_attempts', [
                'attempts_text' => $attemptsText,
            ]);
        }

        return [
            'message' => $message,
            'remaining_attempts' => $remainingAttempts,
        ];
    }
}
