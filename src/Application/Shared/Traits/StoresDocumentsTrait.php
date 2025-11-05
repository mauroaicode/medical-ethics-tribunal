<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use Google\Service\Exception;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Src\Application\Shared\Services\GoogleDriveService;

trait StoresDocumentsTrait
{
    /**
     * Download a document from Google Drive to temporary location
     *
     * @param  string  $fileId  Google Drive file ID
     * @param  string  $fileName  File name to save
     * @return string Temporary file path
     *
     * @throws Exception
     */
    protected function downloadDocumentFromGoogleDrive(
        GoogleDriveService $googleDriveService,
        string $fileId,
        string $fileName
    ): string {
        $tempDir = storage_path('app/temp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Generate unique identifier using random_bytes for security
        $uniqueId = bin2hex(random_bytes(16));
        $tempPath = $tempDir.'/doc_'.$uniqueId.'_'.$fileName;
        $googleDriveService->downloadAsPdf($fileId, $tempPath);

        return $tempPath;
    }

    /**
     * Store document file using Media Library
     *
     * Files will be stored in: {collection_name}/{model_id}/filename
     * Example: process_document/1/PRO-0001_archivo.pdf
     *
     * @param  string  $filePath  Temporary file path
     * @param  string  $collectionName  Media collection name
     * @param  string  $fileName  Original file name
     */
    protected function addDocumentToMediaLibrary(
        HasMedia $model,
        string $filePath,
        string $collectionName,
        string $fileName
    ): Media {
        $media = $model->addMedia($filePath)
            ->usingName($fileName)
            ->usingFileName($fileName)
            ->toMediaCollection($collectionName);

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return $media;
    }
}
