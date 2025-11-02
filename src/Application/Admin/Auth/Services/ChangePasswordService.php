<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Auth\Data\ChangePasswordData;
use Src\Domain\User\Models\User;
use Throwable;

class ChangePasswordService
{
    /**
     * Change user password
     *
     * @throws Throwable
     */
    public function handle(ChangePasswordData $changePasswordData, User $user): User
    {
        return DB::transaction(function () use ($changePasswordData, $user) {
            $user->update([
                'password' => Hash::make($changePasswordData->password),
                'requires_password_change' => false,
            ]);

            return $user->fresh();
        });
    }
}
