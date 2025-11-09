<?php

declare(strict_types=1);

namespace Src\Domain\SessionBlock\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\SessionBlock\Models\SessionBlock;

/** @extends Builder<SessionBlock> */
class SessionBlockQueryBuilder extends Builder
{
    /**
     * Filter blocks by user ID
     */
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Filter blocks by action
     */
    public function forAction(string $action): self
    {
        return $this->where('action', $action);
    }

    /**
     * Filter active blocks (blocked_until is in the future)
     */
    public function active(): self
    {
        return $this->where('blocked_until', '>', now());
    }

    /**
     * Get active block for user and action
     */
    public function activeForUserAndAction(int $userId, string $action): ?SessionBlock
    {
        return $this->forUser($userId)
            ->forAction($action)
            ->active()
            ->first();
    }

    /**
     * Order blocks by blocked_until (most recent first)
     */
    public function orderedByBlockedUntil(): self
    {
        return $this->latest('blocked_until');
    }
}
