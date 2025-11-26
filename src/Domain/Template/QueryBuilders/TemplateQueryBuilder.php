<?php

declare(strict_types=1);

namespace Src\Domain\Template\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Template\Models\Template;

/** @extends Builder<Template> */
class TemplateQueryBuilder extends Builder
{
    /**
     * Exclude soft deleted templates
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Order templates by name
     */
    public function orderedByName(): self
    {
        return $this->orderBy('name');
    }

    /**
     * Order templates by creation date (newest first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest('created_at');
    }

    /**
     * Filter templates by name (search)
     *
     * @param  string|null  $name  The name to search for
     */
    public function filterByName(?string $name): self
    {
        return $this->when($name, function ($query, $searchName): void {
            $keywords = preg_split('/\s+/', trim($searchName));
            $query->where(function (\Illuminate\Contracts\Database\Query\Builder $q) use ($keywords): void {
                foreach ($keywords as $word) {
                    $q->orWhere('name', 'LIKE', "%{$word}%");
                }
            });
        });
    }
}
