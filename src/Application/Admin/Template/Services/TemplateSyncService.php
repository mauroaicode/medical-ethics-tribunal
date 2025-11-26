<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Src\Application\Shared\Services\GoogleDriveService;
use Src\Domain\Template\Models\Template;
use Throwable;

class TemplateSyncService
{
    private GoogleDriveService $googleDriveService;

    /**
     * Sync templates from Google Drive folder
     *
     * @return array<int, Template>
     *
     * @throws Throwable
     */
    public function handle(): array
    {
        return DB::transaction(function (): array {

            $this->googleDriveService = new GoogleDriveService;

            $this->validateAuthentication();

            $folderName = config('template.google_drive_folder_name');
            $folder = $this->findFolder($folderName);

            $files = $this->listFilesInFolder($folder['id'], $folderName);

            return $this->syncTemplates($files, $folder['id']);
        });
    }

    /**
     * Validate Google Drive authentication
     *
     * @throws RuntimeException
     */
    private function validateAuthentication(): void
    {
        if (! $this->googleDriveService->isAuthenticated()) {
            throw new RuntimeException('Google Drive is not authenticated. Please authenticate first.');
        }
    }

    /**
     * Find folder by name in Google Drive
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    private function findFolder(string $folderName): array
    {
        $folder = $this->googleDriveService->findFolderByName($folderName);

        if (! $folder) {
            throw new RuntimeException("Folder '{$folderName}' not found in Google Drive.");
        }

        return $folder;
    }

    /**
     * List files in Google Drive folder
     *
     * @return array<int, array<string, mixed>>
     */
    private function listFilesInFolder(string $folderId, string $folderName): array
    {
        $files = $this->googleDriveService->listFilesInFolder($folderId);

        if ($files === []) {
            Log::channel('google')->warning("No Google Docs files found in folder '{$folderName}' (ID: {$folderId})");
        }

        // Enrich files with web_view_link from metadata
        return array_map(function (array $file): array {
            try {
                $metadata = $this->googleDriveService->getFileMetadata($file['id']);
                $file['web_view_link'] = $metadata['web_view_link'] ?? null;
            } catch (Exception $e) {
                Log::channel('google')->warning('Could not fetch metadata for file', [
                    'file_id' => $file['id'],
                    'file_name' => $file['name'],
                    'error' => $e->getMessage(),
                ]);
                $file['web_view_link'] = null;
            }

            return $file;
        }, $files);
    }

    /**
     * Sync templates from a file array
     *
     * @param  array<int, array<string, mixed>>  $files
     * @return array<int, Template>
     */
    private function syncTemplates(array $files, string $folderId): array
    {
        $syncedTemplates = [];

        foreach ($files as $file) {
            $template = $this->findOrCreateTemplate($file, $folderId);
            $template = $this->updateTemplateIfNeeded($template, $file);

            $syncedTemplates[] = $template;
        }

        return $syncedTemplates;
    }

    /**
     * Find existing template or create new one
     *
     * @param  array<string, mixed>  $file
     */
    private function findOrCreateTemplate(array $file, string $folderId): Template
    {
        $template = Template::query()
            ->where('google_drive_file_id', $file['id'])
            ->first();

        if (! $template) {
            return Template::query()->create([
                'name' => $file['name'],
                'description' => null,
                'google_drive_id' => $folderId,
                'google_drive_file_id' => $file['id'],
                'web_view_link' => $file['web_view_link'] ?? null,
            ]);
        }

        return $template;
    }

    /**
     * Update template if name or web_view_link changed
     *
     * @param  array<string, mixed>  $file
     */
    private function updateTemplateIfNeeded(Template $template, array $file): Template
    {
        $updates = [];

        if ($template->name !== $file['name']) {
            $updates['name'] = $file['name'];
        }

        $webViewLink = $file['web_view_link'] ?? null;
        if ($template->web_view_link !== $webViewLink) {
            $updates['web_view_link'] = $webViewLink;
        }

        if ($updates !== []) {
            $template->update($updates);
            $template->refresh();
        }

        return $template;
    }
}
