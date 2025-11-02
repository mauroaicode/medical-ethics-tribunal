<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\UserStatus;
use Throwable;

class DoctorDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a doctor (soft delete) and deactivate associated user
     *
     * @throws Throwable
     */
    public function handle(Doctor $doctor): Doctor
    {
        return DB::transaction(function () use ($doctor): Doctor {
            $oldValues = $doctor->getAttributes();

            $doctor->load('user');

            $doctor->delete();

            if ($doctor->user) {
                $doctor->user->update([
                    'status' => UserStatus::INACTIVE,
                ]);
            }

            $this->logAudit(
                action: 'delete',
                model: $doctor,
                oldValues: $oldValues,
            );

            return $doctor;
        });
    }
}
