<?php

declare(strict_types=1);

namespace Src\Domain\Session\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Session\Models\Session;

/** @extends Builder<Session> */
class SessionQueryBuilder extends Builder
{
    /**
     * Filter sessions by user ID
     */
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    /**
     * Order sessions by last activity (most recent first)
     */
    public function orderedByLastActivity(): self
    {
        return $this->orderBy('last_activity', 'desc');
    }

    /**
     * Only active sessions (not expired, typically within last 2 hours)
     */
    public function active(): self
    {
        $twoHoursAgo = now()->subHours(2)->timestamp;

        return $this->where('last_activity', '>', $twoHoursAgo);
    }
}
