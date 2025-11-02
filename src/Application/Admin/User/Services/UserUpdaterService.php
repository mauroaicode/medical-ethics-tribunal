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
    public function handle(UpdateUserData $data, User $user): User
    {
        return DB::transaction(function () use ($data, $user) {
            $oldValues = $user->getAttributes();

            $updateData = array_filter([
                'name' => $data->name,
                'last_name' => $data->last_name,
                'document_type' => $data->document_type,
                'document_number' => $data->document_number,
                'phone' => $data->phone,
                'address' => $data->address,
                'email' => $data->email,
                'password' => $data->password,
                'status' => $data->status,
            ], fn ($value) => ! is_null($value));

            $user->update($updateData);

            if (! is_null($data->roles)) {
                $user->syncRoles($data->roles);
            }

            $updatedUser = $user->fresh(['roles']);

            $this->logAudit(
                action: 'update',
                auditable: $updatedUser,
                oldValues: $oldValues,
                newValues: $updatedUser->getAttributes(),
            );

            return $updatedUser;
        });
    }
}
