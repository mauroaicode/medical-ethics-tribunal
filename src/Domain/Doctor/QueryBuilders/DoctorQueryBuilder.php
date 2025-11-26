<?php

declare(strict_types=1);

namespace Src\Domain\Doctor\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\UserStatus;

/** @extends Builder<Doctor> */
class DoctorQueryBuilder extends Builder
{
    /**
     * Include user relationship
     */
    public function withUser(): self
    {
        return $this->with('user');
    }

    /**
     * Include specialty relationship
     */
    public function withSpecialty(): self
    {
        return $this->with('specialty');
    }

    /**
     * Include user and specialty relationships
     */
    public function withRelations(): self
    {
        return $this->with(['user', 'specialty']);
    }

    /**
     * Exclude soft deleted doctors
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Order doctors by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest();
    }

    /**
     * Filter only active doctors (where user status is active)
     */
    public function active(): self
    {
        return $this->whereHas('user', function (\Illuminate\Contracts\Database\Query\Builder $query): void {
            $query->where('status', UserStatus::ACTIVE->value);
        });
    }
}
