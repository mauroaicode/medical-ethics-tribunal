<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Doctor\Data\UpdateDoctorData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\DocumentType;
use Throwable;

class DoctorUpdaterService
{
    use LogsAuditTrait;

    /**
     * Update a doctor and optionally associated user
     *
     * @throws Throwable
     */
    public function handle(UpdateDoctorData $updateDoctorData, Doctor $doctor): Doctor
    {
        return DB::transaction(function () use ($updateDoctorData, $doctor) {
            $oldValues = $doctor->getAttributes();

            // Update user if any user fields are provided
            $userUpdateData = array_filter([
                'name' => $updateDoctorData->name,
                'last_name' => $updateDoctorData->last_name,
                'document_type' => $updateDoctorData->document_type,
                'document_number' => $updateDoctorData->document_number,
                'phone' => $updateDoctorData->phone,
                'address' => $updateDoctorData->address,
                'email' => $updateDoctorData->email,
                'password' => $updateDoctorData->password ? Hash::make($updateDoctorData->password) : null,
            ], fn (DocumentType|string|null $value): bool => ! is_null($value));

            if ($userUpdateData !== []) {
                $doctor->user->update($userUpdateData);
            }

            // Update doctor fields
            $doctorUpdateData = array_filter([
                'specialty_id' => $updateDoctorData->specialty_id,
                'faculty' => $updateDoctorData->faculty,
                'medical_registration_number' => $updateDoctorData->medical_registration_number,
                'medical_registration_place' => $updateDoctorData->medical_registration_place,
                'medical_registration_date' => $updateDoctorData->medical_registration_date,
                'main_practice_company' => $updateDoctorData->main_practice_company,
                'other_practice_company' => $updateDoctorData->other_practice_company,
            ], fn (int|Carbon|string|null $value): bool => ! is_null($value));

            if ($doctorUpdateData !== []) {
                $doctor->update($doctorUpdateData);
            }

            $updatedDoctor = $doctor->fresh(['user', 'specialty']);

            $this->logAudit(
                action: 'update',
                model: $updatedDoctor,
                oldValues: $oldValues,
                newValues: $updatedDoctor->getAttributes(),
            );

            return $updatedDoctor;
        });
    }
}
