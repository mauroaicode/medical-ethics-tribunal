<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Template\Models\Template;

class TemplateFinderService
{
    /**
     * Get all templates
     *
     * @return Collection<int, Template>
     */
    public function handle(): Collection
    {
        return Template::query()
            ->withoutTrashed()
            ->orderBy('name')
            ->get();
    }
}
