<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Application\Shared\Services\GoogleDriveService;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Application\Shared\Traits\StoresDocumentsTrait;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Shared\Enums\FileType;
use Src\Domain\Template\Models\Template;
use Throwable;

class ProcessTemplateDocumentCreatorService
{
    use LogsAuditTrait;
    use StoresDocumentsTrait;

    /**
     * Create a process template document
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

        $googleDriveFileId = $documentData['google_drive_file_id'] ?? null;

        try {
            return DB::transaction(function () use ($process, $template, $documentData) {
                $document = ProcessTemplateDocument::query()->create([
                    'process_id' => $process->id,
                    'template_id' => $template->id,
                    'google_drive_file_id' => $documentData['google_drive_file_id'],
                    'file_name' => $documentData['file_name'],
                    'google_docs_name' => $documentData['google_docs_name'],
                ]);

                if (isset($documentData['temp_path']) && file_exists($documentData['temp_path'])) {
                    $this->addDocumentToMediaLibrary(
                        $document,
                        $documentData['temp_path'],
                        FileType::PROCESS_DOCUMENT->value,
                        $documentData['file_name']
                    );
                }

                $this->logAudit(
                    action: 'create',
                    model: $document,
                    oldValues: null,
                    newValues: $document->getAttributes(),
                );

                return $document;
            });
        } catch (Throwable $e) {
            $this->cleanupGoogleDriveFile($googleDriveFileId);

            throw $e;
        }
    }

    /**
     * Clean up a Google Drive file if document creation fails
     */
    private function cleanupGoogleDriveFile(?string $fileId): void
    {
        if (! $fileId) {
            return;
        }

        try {
            $googleDriveService = new GoogleDriveService;
            $googleDriveService->deleteFile($fileId);
        } catch (Throwable $deleteException) {
            Log::channel('google')->error(
                'Failed to delete Google Drive file after error',
                [
                    'file_id' => $fileId,
                    'error' => $deleteException->getMessage(),
                ]
            );
        }
    }
}
