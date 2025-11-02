<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\User\Data\StoreUserData;
use Src\Application\Shared\Notifications\AccountCreatedNotification;
use Src\Application\Shared\Traits\GeneratesPasswordTrait;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class UserCreatorService
{
    use GeneratesPasswordTrait;
    use LogsAuditTrait;

    /**
     * Create a new user
     *
     * @throws Throwable
     */
    public function handle(StoreUserData $storeUserData): User
    {
        return DB::transaction(function () use ($storeUserData) {

            $temporaryPassword = $this->generateSecurePassword();

            $user = User::query()->create([
                'name' => $storeUserData->name,
                'last_name' => $storeUserData->last_name,
                'document_type' => $storeUserData->document_type,
                'document_number' => $storeUserData->document_number,
                'phone' => $storeUserData->phone,
                'address' => $storeUserData->address,
                'email' => $storeUserData->email,
                'password' => Hash::make($temporaryPassword),
                'status' => $storeUserData->status ?? UserStatus::ACTIVE,
                'requires_password_change' => true,
                'email_verified_at' => now(),
            ]);

            // TODO: When permissions are implemented, check if admin has 'create users' permission

            $user->assignRole($storeUserData->roles);

            $this->logAudit(
                action: 'create',
                model: $user,
                oldValues: null,
                newValues: $user->getAttributes(),
            );

            DB::afterCommit(function () use ($user, $temporaryPassword): void {
                $user->notify(new AccountCreatedNotification($temporaryPassword));
            });

            return $user;
        });
    }
}
