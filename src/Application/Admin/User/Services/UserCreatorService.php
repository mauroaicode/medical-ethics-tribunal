<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Admin\User\Data\StoreUserData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class UserCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new user
     *
     * @throws Throwable
     */
    public function handle(StoreUserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data->name,
                'last_name' => $data->last_name,
                'document_type' => $data->document_type,
                'document_number' => $data->document_number,
                'phone' => $data->phone,
                'address' => $data->address,
                'email' => $data->email,
                'password' => $data->password,
                'status' => $data->status ?? UserStatus::ACTIVE,
            ]);

            // TODO: When permissions are implemented, check if admin has 'create users' permission

            $user->assignRole($data->roles);

            $this->logAudit(
                action: 'create',
                auditable: $user,
                oldValues: null,
                newValues: $user->getAttributes(),
            );

            return $user;
        });
    }
}
