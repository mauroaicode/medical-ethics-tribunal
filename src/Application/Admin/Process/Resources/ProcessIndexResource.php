<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Process\Models\Process;

class ProcessIndexResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public string $process_number,
        public string $status,
        public string $start_date,
        public int $proceedings_count,
        public string $complainant_name,
        public string $complainant_last_name,
        public string $complainant_document_number,
        public string $doctor_name,
        public string $doctor_last_name,
    ) {}

    public static function fromModel(Process $process): self
    {
        $complainantName = '';
        $complainantLastName = '';
        $complainantDocumentNumber = '';

        /** @phpstan-ignore-next-line */
        if ($process->relationLoaded('complainant') && $process->complainant && $process->complainant->user) {
            $complainantName = $process->complainant->user->name;
            $complainantLastName = $process->complainant->user->last_name;
            $complainantDocumentNumber = $process->complainant->user->document_number;
        }

        $doctorName = '';
        $doctorLastName = '';

        /** @phpstan-ignore-next-line */
        if ($process->relationLoaded('doctor') && $process->doctor && $process->doctor->user) {
            $doctorName = $process->doctor->user->name;
            $doctorLastName = $process->doctor->user->last_name;
        }

        return new self(
            id: $process->id,
            name: $process->name,
            process_number: $process->process_number,
            status: $process->status->value,
            start_date: $process->start_date->format('Y-m-d'),
            proceedings_count: $process->proceedings_count ?? ($process->relationLoaded('proceedings') ? $process->proceedings->count() : 0),
            complainant_name: $complainantName,
            complainant_last_name: $complainantLastName,
            complainant_document_number: $complainantDocumentNumber,
            doctor_name: $doctorName,
            doctor_last_name: $doctorLastName,
        );
    }
}
