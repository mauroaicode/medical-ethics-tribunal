<?php

declare(strict_types=1);

namespace Src\Domain\User\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

/** @extends Builder<User> */
class UserQueryBuilder extends Builder
{
    /**
     * Filter users by admin roles (super_admin, admin, secretary)
     *
     * @return Builder<User>
     */
    public function withAdminRoles(): Builder
    {
        return $this->whereHas('roles', function ($query) {
            $query->whereIn('name', UserRole::values());
        });
    }

    /**
     * Include roles relationship
     *
     * @return Builder<User>
     */
    public function withRoles(): Builder
    {
        return $this->with('roles');
    }

    /**
     * Exclude soft deleted users
     *
     * @return Builder<User>
     */
    public function withoutTrashed(): Builder
    {
        return $this->whereNull('deleted_at');
    }
}
