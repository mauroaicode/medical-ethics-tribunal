<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Application\Admin\Template\Data\TemplateFilterData;
use Src\Domain\Template\Models\Template;

class TemplateFinderService
{
    /**
     * Get all templates with filters
     *
     * @param  TemplateFilterData  $filters  The filtering criteria.
     * @return Collection<int, Template>
     */
    public function handle(TemplateFilterData $filters): Collection
    {
        return Template::query()
            ->filterByName($filters->name)
            ->withoutTrashed()
            ->orderedByCreatedAt()
            ->get();
    }
}
