<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Admin\User\Data\UpdateUserData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\User\Models\User;
use Throwable;

class UserUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a user
     *
     * @throws Throwable
     */
    public function handle(UpdateUserData $updateUserData, User $user): User
    {
        return DB::transaction(function () use ($updateUserData, $user) {
            $oldValues = $user->getAttributes();

            $updateData = array_filter([
                'name' => $updateUserData->name,
                'last_name' => $updateUserData->last_name,
                'document_type' => $updateUserData->document_type,
                'document_number' => $updateUserData->document_number,
                'phone' => $updateUserData->phone,
                'address' => $updateUserData->address,
                'email' => $updateUserData->email,
                'password' => $updateUserData->password,
                'status' => $updateUserData->status,
            ], fn (\Src\Domain\User\Enums\DocumentType|string|\Src\Domain\User\Enums\UserStatus|null $value): bool => ! is_null($value));

            $user->update($updateData);

            if (! is_null($updateUserData->roles)) {
                $user->syncRoles($updateUserData->roles);
            }

            $updatedUser = $user->fresh(['roles']);

            $this->logAudit(
                action: 'update',
                model: $updatedUser,
                oldValues: $oldValues,
                newValues: $updatedUser->getAttributes(),
            );

            return $updatedUser;
        });
    }
}
