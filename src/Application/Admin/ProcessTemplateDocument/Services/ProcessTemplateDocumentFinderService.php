<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Services;

use Illuminate\Database\Eloquent\Collection;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;

class ProcessTemplateDocumentFinderService
{
    /**
     * Get all template documents for a process
     *
     * @return Collection<int, ProcessTemplateDocument>
     */
    public function handle(Process $process): Collection
    {
        return ProcessTemplateDocument::query()
            ->forProcess($process->id)
            ->withRelations()
            ->orderedByCreatedAt()
            ->get();
    }
}
