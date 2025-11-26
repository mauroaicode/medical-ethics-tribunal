<?php

declare(strict_types=1);

namespace Src\Domain\Process\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;
use Src\Application\Admin\Process\Data\ProcessFilterData;
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

    /**
     * Order processes by start_date (most recent first)
     */
    public function orderedByStartDate(): self
    {
        return $this->latest('start_date');
    }

    /**
     * Apply filters to the process query.
     *
     * @param  ProcessFilterData  $data  The filtering criteria.
     */
    public function filters(ProcessFilterData $data): self
    {
        return $this
            ->when($data->process_number, function ($query, $processNumber): void {
                $query->where('process_number', 'LIKE', "%{$processNumber}%");
            })
            ->when($data->complainant_document_number, function ($query, $documentNumber): void {
                $query->whereHas('complainant.user', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($documentNumber): void {
                    $q->where('document_number', 'LIKE', "%{$documentNumber}%");
                });
            })
            ->when($data->doctor_name, function ($query, $doctorName): void {
                $keywords = preg_split('/\s+/', trim($doctorName));
                $query->whereHas('doctor.user', function (\Illuminate\Contracts\Database\Query\Builder $q) use ($keywords): void {
                    foreach ($keywords as $word) {
                        $q->where(function (\Illuminate\Contracts\Database\Query\Builder $subQuery) use ($word): void {
                            $subQuery->where('name', 'LIKE', "%{$word}%")
                                ->orWhere('last_name', 'LIKE', "%{$word}%");
                        });
                    }
                });
            })
            ->when($data->start_date, function ($query, $startDate): void {
                $query->whereDate('start_date', $startDate->format('Y-m-d'));
            })
            ->when($data->start_date_from || $data->start_date_to, function ($query) use ($data): void {
                if ($data->start_date_from && $data->start_date_to) {
                    // Rango completo: desde fecha hasta fecha
                    $query->whereBetween('start_date', [
                        $data->start_date_from->format('Y-m-d'),
                        $data->start_date_to->format('Y-m-d'),
                    ]);
                } elseif ($data->start_date_from instanceof \Carbon\Carbon) {
                    // Solo fecha desde
                    $query->whereDate('start_date', '>=', $data->start_date_from->format('Y-m-d'));
                } elseif ($data->start_date_to instanceof \Carbon\Carbon) {
                    // Solo fecha hasta
                    $query->whereDate('start_date', '<=', $data->start_date_to->format('Y-m-d'));
                }
            })
            ->when($data->status, function ($query, $status): void {
                $query->where('status', $status->value);
            });
    }
}
