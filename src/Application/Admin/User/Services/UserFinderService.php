<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\User\Models\User;

class UserFinderService
{
    /**
     * Get all users with admin roles
     *
     * @return Collection<int, User>
     */
    public function handle(): Collection
    {
        return User::query()
            ->withAdminRoles()
            ->withRoles()
            ->withoutTrashed()
            ->get();
    }
}
