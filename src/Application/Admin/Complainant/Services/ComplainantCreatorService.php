<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Src\Application\Admin\Complainant\Data\StoreComplainantData;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Enums\UserStatus;
use Src\Domain\User\Models\User;
use Throwable;

class ComplainantCreatorService
{
    use LogsAuditTrait;

    /**
     * Create a new complainant and associated user (without password)
     *
     * @throws Throwable
     */
    public function handle(StoreComplainantData $storeComplainantData): Complainant
    {
        return DB::transaction(function () use ($storeComplainantData) {
            // Create user without password (complainant never accesses the system)
            // Use a random hash as password since password field is required but won't be used
            $user = User::query()->create([
                'name' => $storeComplainantData->name,
                'last_name' => $storeComplainantData->last_name,
                'document_type' => $storeComplainantData->document_type,
                'document_number' => $storeComplainantData->document_number,
                'phone' => $storeComplainantData->phone,
                'address' => $storeComplainantData->address,
                'email' => $storeComplainantData->email,
                'password' => Hash::make(Str::random(32)), // Random password, never used
                'status' => UserStatus::INACTIVE, // Complainants are inactive users
                'requires_password_change' => false,
                'email_verified_at' => null, // No email verification needed
            ]);

            $complainant = Complainant::query()->create([
                'user_id' => $user->id,
                'city_id' => $storeComplainantData->city_id,
                'municipality' => $storeComplainantData->municipality,
                'company' => $storeComplainantData->company,
                'is_anonymous' => $storeComplainantData->is_anonymous,
            ]);

            $this->logAudit(
                action: 'create',
                model: $complainant,
                oldValues: null,
                newValues: $complainant->getAttributes(),
            );

            return $complainant->load(['user', 'city']);
        });
    }
}
