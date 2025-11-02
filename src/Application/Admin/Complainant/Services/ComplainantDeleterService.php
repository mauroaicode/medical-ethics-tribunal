<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Enums\UserStatus;
use Throwable;

class ComplainantDeleterService
{
    use LogsAuditTrait;

    /**
     * Delete a complainant (soft delete) and deactivate associated user
     *
     * @throws Throwable
     */
    public function handle(Complainant $complainant): Complainant
    {
        return DB::transaction(function () use ($complainant): Complainant {
            $oldValues = $complainant->getAttributes();

            $complainant->load('user');

            $complainant->delete();

            if ($complainant->user) {
                $complainant->user->update([
                    'status' => UserStatus::INACTIVE,
                ]);
            }

            $this->logAudit(
                action: 'delete',
                model: $complainant,
                oldValues: $oldValues,
            );

            return $complainant;
        });
    }
}
