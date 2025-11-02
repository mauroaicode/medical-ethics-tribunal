<?php

declare(strict_types=1);

namespace Src\Domain\Complainant\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Complainant\Models\Complainant;

/** @extends Builder<Complainant> */
class ComplainantQueryBuilder extends Builder
{
    /**
     * Include user relationship
     */
    public function withUser(): self
    {
        return $this->with('user');
    }

    /**
     * Include city relationship
     */
    public function withCity(): self
    {
        return $this->with('city');
    }

    /**
     * Include all relationships
     */
    public function withRelations(): self
    {
        return $this->with(['user', 'city']);
    }

    /**
     * Exclude soft deleted complainants
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Order complainants by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest();
    }
}
