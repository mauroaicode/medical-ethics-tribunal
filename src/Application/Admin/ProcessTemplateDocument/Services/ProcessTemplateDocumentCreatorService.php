<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Services;

use Illuminate\Support\Facades\DB;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;
use Throwable;

class ProcessTemplateDocumentCreatorService
{
    use LogsAuditTrait;

    /**
     * Create process template document
     *
     * @param  array<string, mixed>  $documentData
     *
     * @throws Throwable
     */
    public function handle(
        Process $process,
        Template $template,
        array $documentData
    ): ProcessTemplateDocument {
        return DB::transaction(function () use ($process, $template, $documentData) {
            $document = ProcessTemplateDocument::query()->create([
                'process_id' => $process->id,
                'template_id' => $template->id,
                'google_drive_file_id' => $documentData['google_drive_file_id'],
                'file_name' => $documentData['file_name'],
                'local_path' => $documentData['local_path'],
                'google_docs_name' => $documentData['google_docs_name'],
            ]);

            // Log audit entry
            $this->logAudit(
                action: 'create',
                model: $document,
                oldValues: null,
                newValues: $document->getAttributes(),
            );

            return $document;
        });
    }
}
