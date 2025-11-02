<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Magistrate\Data\StoreMagistrateData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class MagistrateCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new magistrate and associated user
     *
     * @throws Throwable
     */
    public function handle(StoreMagistrateData $storeMagistrateData): Magistrate
    {
        return DB::transaction(function () use ($storeMagistrateData) {
            // Create user first
            $user = User::query()->create([
                'name' => $storeMagistrateData->name,
                'last_name' => $storeMagistrateData->last_name,
                'document_type' => $storeMagistrateData->document_type,
                'document_number' => $storeMagistrateData->document_number,
                'phone' => $storeMagistrateData->phone,
                'address' => $storeMagistrateData->address,
                'email' => $storeMagistrateData->email,
                'password' => Hash::make($storeMagistrateData->password),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]);

            // Create magistrate
            $magistrate = Magistrate::query()->create([
                'user_id' => $user->id,
            ]);

            $this->logAudit(
                action: 'create',
                model: $magistrate,
                oldValues: null,
                newValues: $magistrate->getAttributes(),
            );

            return $magistrate->load('user');
        });
    }
}
