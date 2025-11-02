<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Enums\UserStatus;
use Throwable;

class MagistrateDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a magistrate (soft delete) and deactivate associated user
     *
     * @throws Throwable
     */
    public function handle(Magistrate $magistrate): Magistrate
    {
        return DB::transaction(function () use ($magistrate): Magistrate {
            $oldValues = $magistrate->getAttributes();

            $magistrate->load('user');

            $magistrate->delete();

            if ($magistrate->user) {
                $magistrate->user->update([
                    'status' => UserStatus::INACTIVE,
                ]);
            }

            $this->logAudit(
                action: 'delete',
                model: $magistrate,
                oldValues: $oldValues,
            );

            return $magistrate;
        });
    }
}
