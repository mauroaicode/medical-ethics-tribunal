<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Doctor\Data\StoreDoctorData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class DoctorCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new doctor and associated user
     *
     * @throws Throwable
     */
    public function handle(StoreDoctorData $storeDoctorData): Doctor
    {
        return DB::transaction(function () use ($storeDoctorData) {

            $user = User::query()->create([
                'name' => $storeDoctorData->name,
                'last_name' => $storeDoctorData->last_name,
                'document_type' => $storeDoctorData->document_type,
                'document_number' => $storeDoctorData->document_number,
                'phone' => $storeDoctorData->phone,
                'address' => $storeDoctorData->address,
                'email' => $storeDoctorData->email,
                'password' => Hash::make($storeDoctorData->password),
                'status' => UserStatus::ACTIVE,
                'email_verified_at' => now(),
            ]);

            $doctor = Doctor::query()->create([
                'user_id' => $user->id,
                'specialty_id' => $storeDoctorData->specialty_id,
                'faculty' => $storeDoctorData->faculty,
                'medical_registration_number' => $storeDoctorData->medical_registration_number,
                'medical_registration_place' => $storeDoctorData->medical_registration_place,
                'medical_registration_date' => $storeDoctorData->medical_registration_date,
                'main_practice_company' => $storeDoctorData->main_practice_company,
                'other_practice_company' => $storeDoctorData->other_practice_company,
            ]);

            $this->logAudit(
                action: 'create',
                model: $doctor,
                oldValues: null,
                newValues: $doctor->getAttributes(),
            );

            return $doctor->load(['user', 'specialty']);
        });
    }
}
