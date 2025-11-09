<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Services;

use Src\Application\Admin\StepUp\Exceptions\UserBlockedException;
use Src\Domain\SessionBlock\Models\SessionBlock;
use Src\Domain\User\Models\User;

class CheckUserBlockService
{
    /**
     * Check if user has any active block (for login verification)
     *
     * @throws UserBlockedException
     */
    public function handle(User $user): void
    {
        $activeBlock = SessionBlock::query()
            ->forUser($user->id)
            ->active()
            ->first();

        if ($activeBlock) {
            throw new UserBlockedException($activeBlock);
        }
    }
}
