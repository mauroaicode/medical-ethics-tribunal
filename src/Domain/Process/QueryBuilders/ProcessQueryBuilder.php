<?php

declare(strict_types=1);

namespace Src\Domain\Process\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Domain\Process\Models\Process;

/** @extends Builder<Process> */
class ProcessQueryBuilder extends Builder
{
    /**
     * Include complainant relationship
     */
    public function withComplainant(): self
    {
        return $this->with('complainant');
    }

    /**
     * Include doctor relationship
     */
    public function withDoctor(): self
    {
        return $this->with('doctor');
    }

    /**
     * Include magistrate instructor relationship
     */
    public function withMagistrateInstructor(): self
    {
        return $this->with('magistrateInstructor');
    }

    /**
     * Include magistrate ponente relationship
     */
    public function withMagistratePonente(): self
    {
        return $this->with('magistratePonente');
    }

    /**
     * Include template documents relationship
     */
    public function withTemplateDocuments(): self
    {
        return $this->with(['templateDocuments.media', 'templateDocuments.template']);
    }

    /**
     * Include all relationships
     */
    public function withRelations(): self
    {
        return $this->with([
            'complainant.user',
            'complainant.city',
            'doctor.user',
            'doctor.specialty',
            'magistrateInstructor.user',
            'magistratePonente.user',
            'templateDocuments.media',
            'templateDocuments.template',
        ]);
    }

    /**
     * Exclude soft deleted processes
     */
    public function withoutTrashed(): self
    {
        return $this->whereNull('deleted_at');
    }

    /**
     * Order processes by created_at (most recent first)
     */
    public function orderedByCreatedAt(): self
    {
        return $this->latest();
    }
}
