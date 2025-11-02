<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Doctor\Data\StoreDoctorData;
use Src\Application\Shared\Notifications\AccountCreatedNotification;
use Src\Application\Shared\Traits\DoctorMagistratePasswordTrait;
use Src\Application\Shared\Traits\GeneratesPasswordTrait;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class DoctorCreatorService
{
    use DoctorMagistratePasswordTrait;
    use GeneratesPasswordTrait;
    use LogsAuditTrait;

    /**
     * Create a new doctor and associated user
     *
     * @throws Throwable
     */
    public function handle(StoreDoctorData $storeDoctorData): Doctor
    {
        return DB::transaction(function () use ($storeDoctorData) {

            $passwordData = $this->getDoctorMagistratePassword();

            $password = $passwordData['password'];
            $shouldSendEmail = $passwordData['should_send_email'];

            $user = User::query()->create([
                'name' => $storeDoctorData->name,
                'last_name' => $storeDoctorData->last_name,
                'document_type' => $storeDoctorData->document_type,
                'document_number' => $storeDoctorData->document_number,
                'phone' => $storeDoctorData->phone,
                'address' => $storeDoctorData->address,
                'email' => $storeDoctorData->email,
                'password' => Hash::make($password),
                'status' => UserStatus::ACTIVE,
                'requires_password_change' => $shouldSendEmail,
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

            // Send email notification after commit only if enabled
            if ($shouldSendEmail) {
                DB::afterCommit(function () use ($user, $password): void {
                    $user->notify(new AccountCreatedNotification($password));
                });
            }

            return $doctor->load(['user', 'specialty']);
        });
    }
}
