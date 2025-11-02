<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Admin\Complainant\Data\UpdateComplainantData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Enums\DocumentType;
use Throwable;

class ComplainantUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a complainant and optionally associated user
     *
     * @throws Throwable
     */
    public function handle(UpdateComplainantData $updateComplainantData, Complainant $complainant): Complainant
    {
        return DB::transaction(function () use ($updateComplainantData, $complainant) {
            $oldValues = $complainant->getAttributes();

            // Update user fields if provided
            $userUpdateData = array_filter([
                'name' => $updateComplainantData->name,
                'last_name' => $updateComplainantData->last_name,
                'document_type' => $updateComplainantData->document_type,
                'document_number' => $updateComplainantData->document_number,
                'phone' => $updateComplainantData->phone,
                'address' => $updateComplainantData->address,
                'email' => $updateComplainantData->email,
            ], fn (DocumentType|string|null $value): bool => ! is_null($value));

            if ($userUpdateData !== []) {
                $complainant->user->update($userUpdateData);
            }

            // Update complainant fields
            $complainantUpdateData = array_filter([
                'city_id' => $updateComplainantData->city_id,
                'municipality' => $updateComplainantData->municipality,
                'company' => $updateComplainantData->company,
                'is_anonymous' => $updateComplainantData->is_anonymous,
            ], fn (mixed $value): bool => ! is_null($value));

            if ($complainantUpdateData !== []) {
                $complainant->update($complainantUpdateData);
            }

            $updatedComplainant = $complainant->fresh(['user', 'city']);

            $this->logAudit(
                action: 'update',
                model: $updatedComplainant,
                oldValues: $oldValues,
                newValues: $updatedComplainant->getAttributes(),
            );

            return $updatedComplainant;
        });
    }
}
