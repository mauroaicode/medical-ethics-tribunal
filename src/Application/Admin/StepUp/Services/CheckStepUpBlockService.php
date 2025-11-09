<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Services;

use Src\Application\Admin\StepUp\Exceptions\UserBlockedException;
use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;

class CheckStepUpBlockService
{
    /**
     * Check if user is blocked for a specific action
     *
     * @throws UserBlockedException
     */
    public function handle(User $user, string $action): void
    {
        $activeBlock = SessionBlock::query()
            ->activeForUserAndAction($user->id, $action);

        if ($activeBlock) {
            throw new UserBlockedException($activeBlock);
        }
    }
}
