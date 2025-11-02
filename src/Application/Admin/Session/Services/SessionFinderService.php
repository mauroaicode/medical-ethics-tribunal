<?php

declare(strict_types=1);

namespace Src\Application\Admin\Session\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Session\Models\Session;

class SessionFinderService
{
    /**
     * Get all sessions ordered by last activity
     *
     * @return Collection<int, Session>
     */
    public function handle(): Collection
    {
        return Session::query()
            ->with('user')
            ->orderedByLastActivity()
            ->get();
    }
}
