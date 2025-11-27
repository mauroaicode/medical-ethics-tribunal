<?php

declare(strict_types=1);

namespace Src\Domain\ProcessTemplateDocument\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;

/** @extends Builder<ProcessTemplateDocument> */
class ProcessTemplateDocumentQueryBuilder extends Builder
{
    /**
     * Filter by process ID
     */
    public function forProcess(int $processId): self
    {
        return $this->where('process_id', $processId);
    }

    /**
     * Include template relationship
     */
    public function withTemplate(): self
    {
        return $this->with('template');
    }

    /**
     * Include media relationship
     */
    public function withMedia(): self
    {
        return $this->with('media');
    }

    /**
     * Include all relationships
     */
    public function withRelations(): self
    {
        return $this->with(['template', 'media']);
    }

    /**
     * Order by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest('created_at');
    }
}
