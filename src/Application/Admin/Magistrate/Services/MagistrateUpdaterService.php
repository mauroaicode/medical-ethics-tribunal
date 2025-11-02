<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Magistrate\Data\UpdateMagistrateData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Enums\DocumentType;
use Throwable;

class MagistrateUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a magistrate and optionally associated user
     *
     * @throws Throwable
     */
    public function handle(UpdateMagistrateData $updateMagistrateData, Magistrate $magistrate): Magistrate
    {
        return DB::transaction(function () use ($updateMagistrateData, $magistrate) {

            $oldValues = $magistrate->getAttributes();

            $userUpdateData = array_filter([
                'name' => $updateMagistrateData->name,
                'last_name' => $updateMagistrateData->last_name,
                'document_type' => $updateMagistrateData->document_type,
                'document_number' => $updateMagistrateData->document_number,
                'phone' => $updateMagistrateData->phone,
                'address' => $updateMagistrateData->address,
                'email' => $updateMagistrateData->email,
                'password' => $updateMagistrateData->password ? Hash::make($updateMagistrateData->password) : null,
            ], fn (DocumentType|string|null $value): bool => ! is_null($value));

            if ($userUpdateData !== []) {
                $magistrate->user->update($userUpdateData);
            }

            $updatedMagistrate = $magistrate->fresh('user');

            $this->logAudit(
                action: 'update',
                model: $updatedMagistrate,
                oldValues: $oldValues,
                newValues: $updatedMagistrate->getAttributes(),
            );

            return $updatedMagistrate;
        });
    }
}
