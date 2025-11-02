<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Magistrate\Data\StoreMagistrateData;
use Src\Application\Shared\Notifications\AccountCreatedNotification;
use Src\Application\Shared\Traits\DoctorMagistratePasswordTrait;
use Src\Application\Shared\Traits\GeneratesPasswordTrait;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class MagistrateCreatorService
{
    use DoctorMagistratePasswordTrait;
    use GeneratesPasswordTrait;
    use LogsAuditTrait;

    /**
     * Create a new magistrate and associated user
     *
     * @throws Throwable
     */
    public function handle(StoreMagistrateData $storeMagistrateData): Magistrate
    {
        return DB::transaction(function () use ($storeMagistrateData) {

            $passwordData = $this->getDoctorMagistratePassword();

            $password = $passwordData['password'];
            $shouldSendEmail = $passwordData['should_send_email'];

            $user = User::query()->create([
                'name' => $storeMagistrateData->name,
                'last_name' => $storeMagistrateData->last_name,
                'document_type' => $storeMagistrateData->document_type,
                'document_number' => $storeMagistrateData->document_number,
                'phone' => $storeMagistrateData->phone,
                'address' => $storeMagistrateData->address,
                'email' => $storeMagistrateData->email,
                'password' => Hash::make($password),
                'status' => UserStatus::ACTIVE,
                'requires_password_change' => $shouldSendEmail,
                'email_verified_at' => now(),
            ]);

            $magistrate = Magistrate::query()->create([
                'user_id' => $user->id,
            ]);

            $this->logAudit(
                action: 'create',
                model: $magistrate,
                oldValues: null,
                newValues: $magistrate->getAttributes(),
            );

            // Send email notification after commit only if enabled
            if ($shouldSendEmail) {
                DB::afterCommit(function () use ($user, $password): void {
                    $user->notify(new AccountCreatedNotification($password));
                });
            }

            return $magistrate->load('user');
        });
    }
}
