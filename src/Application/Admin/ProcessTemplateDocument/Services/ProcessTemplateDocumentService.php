<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Services;

use Illuminate\Support\Str;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;

class ProcessTemplateDocumentService
{
    /**
     * Validate template and process, generate file name and validate if document already exists
     *
     * @return array{template: Template, process: Process, file_name: string, exists: bool}
     */
    public function handle(int $templateId, int $processId): array
    {
        $template = $this->findTemplate($templateId);
        $process = $this->findProcess($processId);

        $this->loadProcessRelationships($process);

        $fileName = $this->formatFileName($process);
        $exists = $this->documentExists($process->id, $fileName);

        return [
            'template' => $template,
            'process' => $process,
            'file_name' => $fileName,
            'exists' => $exists,
        ];
    }

    /**
     * Find template by ID
     */
    private function findTemplate(int $templateId): Template
    {
        return Template::query()->findOrFail($templateId);
    }

    /**
     * Find process by ID
     */
    private function findProcess(int $processId): Process
    {
        return Process::query()->findOrFail($processId);
    }

    /**
     * Load process relationships needed for processing
     */
    private function loadProcessRelationships(Process $process): void
    {
        $process->load([
            'complainant.user',
            'complainant.city',
            'doctor.user',
            'doctor.specialty',
            'magistrateInstructor.user',
            'magistratePonente.user',
        ]);
    }

    /**
     * Format file name from a process
     */
    private function formatFileName(Process $process): string
    {
        $cleanName = rtrim($process->name, '.');
        $formatProcessName = Str::of($cleanName)->replace(' ', '_');

        return "{$process->process_number}_{$formatProcessName}.docx";
    }

    /**
     * Check if a document already exists for process and file name
     */
    private function documentExists(int $processId, string $fileName): bool
    {
        return ProcessTemplateDocument::query()
            ->where('process_id', $processId)
            ->where('file_name', $fileName)
            ->exists();
    }
}
