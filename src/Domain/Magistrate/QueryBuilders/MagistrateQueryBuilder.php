<?php

declare(strict_types=1);

namespace Src\Domain\Magistrate\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Magistrate\Models\Magistrate;

/** @extends Builder<Magistrate> */
class MagistrateQueryBuilder extends Builder
{
    /**
     * Include user relationship
     */
    public function withUser(): self
    {
        return $this->with('user');
    }

    /**
     * Include all relationships
     */
    public function withRelations(): self
    {
        return $this->with(['user']);
    }

    /**
     * Exclude soft deleted magistrates
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Order magistrates by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest();
    }
}
