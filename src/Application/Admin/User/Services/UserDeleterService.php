<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class UserDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a user (soft delete)
     *
     * @throws Throwable
     */
    public function handle(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            $oldValues = $user->getAttributes();

            $user->update([
                'status' => UserStatus::INACTIVE,
            ]);

            $user->delete();

            $this->logAudit(
                action: 'delete',
                model: $user,
                oldValues: $oldValues,
            );

            return $user;
        });
    }
}
