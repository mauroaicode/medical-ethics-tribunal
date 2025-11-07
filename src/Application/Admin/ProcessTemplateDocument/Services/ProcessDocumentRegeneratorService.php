<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Src\Application\Admin\Template\Services\TemplateProcessorService;
use Src\Application\Shared\Services\GoogleDriveService;
use Src\Application\Shared\Traits\LogsAuditTrait;
use Src\Domain\Process\Models\Process;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Throwable;

class ProcessDocumentRegeneratorService
{
    use LogsAuditTrait;

    /**
     * Regenerate all documents for a process
     * Deletes existing documents (media and Google Drive) and regenerates them
     *
     * @throws Throwable
     */
    public function handle(Process $process): void
    {
        $process->load('templateDocuments.template');

        if ($process->templateDocuments->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($process): void {
            foreach ($process->templateDocuments as $templateDocument) {
                $this->regenerateDocument($templateDocument);
            }
        });
    }

    /**
     * Regenerate a single document
     *
     * @throws Throwable
     */
    private function regenerateDocument(ProcessTemplateDocument $templateDocument): void
    {
        $template = $templateDocument->template;
        $process = $templateDocument->process;

        $process->load([
            'complainant.user',
            'complainant.city',
            'doctor.user',
            'doctor.specialty',
            'magistrateInstructor.user',
            'magistratePonente.user',
        ]);

        $googleDriveFileId = $templateDocument->google_drive_file_id;

        // Generate new file name based on updated process data
        $processTemplateDocumentService = new ProcessTemplateDocumentService;
        $validation = $processTemplateDocumentService->handle($template->id, $process->id);
        $newFileName = $validation['file_name'];

        // Delete existing media
        $this->deleteDocumentMedia($templateDocument);

        // Delete a Google Drive file
        $this->deleteGoogleDriveFile($googleDriveFileId);

        // Regenerate document using TemplateProcessorService
        $templateProcessorService = new TemplateProcessorService;
        $documentData = $templateProcessorService->handle(
            $template,
            $process,
            $newFileName
        );

        // Update the existing document record with new data
        $templateDocument->update([
            'google_drive_file_id' => $documentData['google_drive_file_id'],
            'file_name' => $documentData['file_name'],
            'google_docs_name' => $documentData['google_docs_name'],
        ]);

        // Add new media to the document
        if (isset($documentData['temp_path']) && file_exists($documentData['temp_path'])) {
            $tempPath = $documentData['temp_path'];

            $templateDocument->addMedia($tempPath)
                ->usingName($documentData['file_name'])
                ->usingFileName($documentData['file_name'])
                ->toMediaCollection($templateDocument->getMediaCollectionName());

            // Clean up temp file if it still exists
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }

        // Log audit entry
        $this->logAudit(
            action: 'update',
            model: $templateDocument,
            oldValues: ['regenerated' => true],
            newValues: $templateDocument->getAttributes(),
        );
    }

    /**
     * Delete media files from a media library
     */
    private function deleteDocumentMedia(ProcessTemplateDocument $templateDocument): void
    {
        try {
            $collectionName = $templateDocument->getMediaCollectionName();
            $media = $templateDocument->getMedia($collectionName);

            foreach ($media as $mediaItem) {
                $mediaItem->delete();
            }
        } catch (Throwable $e) {
            Log::error('Error deleting media from document', [
                'document_id' => $templateDocument->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a file from Google Drive
     */
    private function deleteGoogleDriveFile(?string $fileId): void
    {
        if (! $fileId) {
            return;
        }

        try {
            $googleDriveService = new GoogleDriveService;

            if (! $googleDriveService->isAuthenticated()) {
                Log::channel('google')->warning('Cannot delete Google Drive file: not authenticated', [
                    'file_id' => $fileId,
                ]);

                return;
            }

            $googleDriveService->deleteFile($fileId);
        } catch (Throwable $e) {
            Log::channel('google')->error('Error deleting file from Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
