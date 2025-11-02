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
     */
    public function withAdminRoles(): self
    {
        return $this->whereHas('roles', function (\Illuminate\Contracts\Database\Query\Builder $builder): void {
            $builder->whereIn('name', UserRole::values());
        });
    }

    /**
     * Include roles relationship
     */
    public function withRoles(): self
    {
        return $this->with('roles');
    }

    /**
     * Exclude soft deleted users
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }
}
