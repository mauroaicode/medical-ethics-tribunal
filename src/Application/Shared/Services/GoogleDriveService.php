<?php

declare(strict_types=1);

namespace Src\Application\Shared\Services;

use Google\Client;
use Google\Service\Docs;
use Google\Service\Docs\BatchUpdateDocumentRequest;
use Google\Service\Docs\Request;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Exception;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveService
{
    private ?Client $client = null;

    private ?Drive $driveService = null;

    private ?Docs $docsService = null;

    private bool $initialized = false;

    public function __construct()
    {
        // Initialize lazily to allow mocking in tests
        // The service can be instantiated without credentials for testing
    }

    /**
     * Save refresh token for future use
     */
    public function saveRefreshToken(string $refreshToken): void
    {
        $tokenPath = storage_path('app/google-drive-token.json');
        $directory = dirname($tokenPath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($tokenPath, json_encode([
            'refresh_token' => $refreshToken,
            'created' => now()->toIso8601String(),
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get authorization URL for OAuth flow
     */
    public function getAuthorizationUrl(): string
    {
        return $this->getClient()->createAuthUrl();
    }

    /**
     * Authenticate with authorization code from OAuth callback
     *
     * @throws \Exception
     */
    public function authenticate(string $authCode): void
    {
        try {
            $accessToken = $this->getClient()->fetchAccessTokenWithAuthCode($authCode);

            if (array_key_exists('error', $accessToken)) {
                Log::channel('google')->error('Error fetching access token from Google', [
                    'error' => $accessToken['error'],
                    'error_description' => $accessToken['error_description'] ?? null,
                ]);
                throw new RuntimeException('Error fetching access token: '.($accessToken['error_description'] ?? $accessToken['error']));
            }

            $this->getClient()->setAccessToken($accessToken);

            // Save refresh token if provided
            if (isset($accessToken['refresh_token'])) {
                $this->saveRefreshToken($accessToken['refresh_token']);
                Log::channel('google')->info('Refresh token saved successfully');
            } else {
                Log::channel('google')->warning('No refresh token provided in access token response');
            }
        } catch (\Exception $e) {
            Log::channel('google')->error('Authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Find folder by name in Google Drive
     *
     * @return array<string, mixed>|null
     */
    public function findFolderByName(string $folderName): ?array
    {
        // Ensure we have a valid access token
        if ($this->getClient()->isAccessTokenExpired()) {
            $refreshToken = $this->getRefreshToken();
            if ($refreshToken) {
                $this->getClient()->refreshToken($refreshToken);
                Log::channel('google')->info('Access token refreshed');
            } else {
                Log::channel('google')->error('Access token expired and no refresh token available');
                throw new RuntimeException('Access token expired and no refresh token available. Please re-authenticate.');
            }
        }

        $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";

        try {

            $results = $this->getDriveService()->files->listFiles([
                'q' => $query,
                'fields' => 'files(id, name, mimeType)',
            ]);

            if (empty($results->getFiles())) {
                Log::channel('google')->warning('Folder not found', ['folder_name' => $folderName]);

                return null;
            }

            $folder = $results->getFiles()[0];

            return [
                'id' => $folder->getId(),
                'name' => $folder->getName(),
            ];
        } catch (Exception $e) {

            Log::channel('google')->error('Error finding folder', [
                'folder_name' => $folderName,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Error finding folder in Google Drive: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * List Google Docs files and Word documents in a folder
     *
     * @param  string  $folderId  Folder ID in Google Drive
     * @param  bool  $includeWordDocs  Whether to include uploaded Word documents (.docx)
     * @return array<int, array<string, mixed>>
     */
    public function listFilesInFolder(string $folderId, bool $includeWordDocs = true): array
    {
        if ($this->getClient()->isAccessTokenExpired()) {
            $refreshToken = $this->getRefreshToken();
            if ($refreshToken) {
                $this->getClient()->refreshToken($refreshToken);
            } else {
                throw new RuntimeException('Access token expired and no refresh token available. Please re-authenticate.');
            }
        }

        // Build a query: files in the folder that are either Google Docs or Word documents and not trashed
        if ($includeWordDocs) {
            // Search for both Google Docs and Word documents
            $templateMimeTypes = config('google-docs.template_mime_types');
            $mimeTypeConditions = collect($templateMimeTypes)
                ->map(fn (string $mimeType): string => "mimeType='{$mimeType}'")
                ->implode(' or ');

            $query = "'{$folderId}' in parents and trashed=false and ({$mimeTypeConditions})";
        } else {
            // Only Google Docs
            $googleDocsMimeType = config('google-docs.mime_types.google_docs');
            $query = "'{$folderId}' in parents and mimeType='{$googleDocsMimeType}' and trashed=false";
        }

        try {
            $results = $this->getDriveService()->files->listFiles([
                'q' => $query,
                'spaces' => 'drive',
                'fields' => 'nextPageToken, files(id, name, mimeType, modifiedTime, createdTime)',
                'orderBy' => 'name',
                'pageSize' => 100,
            ]);

            $files = [];
            $fileList = $results->getFiles();

            if ($fileList) {
                foreach ($fileList as $file) {
                    $files[] = [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'mime_type' => $file->getMimeType(),
                        'modified_time' => $file->getModifiedTime() ?: null,
                        'created_time' => $file->getCreatedTime() ?: null,
                    ];
                }
            }

            return $files;
        } catch (Exception $e) {
            Log::channel('google')->error('Error listing files from folder', [
                'folder_id' => $folderId,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Error listing files from Google Drive: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Copy a Google Docs file or Word document
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function copyFile(string $fileId, string $newName, ?string $destinationFolderId = null): array
    {
        $copiedFile = new DriveFile;
        $copiedFile->setName($newName);

        $copyParams = [];
        if ($destinationFolderId) {
            $copyParams['fields'] = 'id, name, parents, mimeType';
            $copiedFile->setParents([$destinationFolderId]);
        } else {
            $copyParams['fields'] = 'id, name, mimeType';
        }

        $newFile = $this->getDriveService()->files->copy($fileId, $copiedFile, $copyParams);

        return [
            'id' => $newFile->getId(),
            'name' => $newFile->getName(),
            'mime_type' => $newFile->getMimeType(),
        ];
    }

    /**
     * Convert Word document to Google Docs format
     *
     * @return string Google Docs file ID
     *
     * @throws Exception
     */
    public function convertWordToGoogleDocs(string $wordFileId, string $newName, ?string $destinationFolderId = null): string
    {
        // Get the Word file metadata
        $this->getDriveService()->files->get($wordFileId, ['fields' => 'mimeType, parents']);

        // Copy the file with Google Docs MIME type to convert it
        $copiedFile = new DriveFile;
        $copiedFile->setName($newName);
        $copiedFile->setMimeType('application/vnd.google-apps.document');

        $copyParams = ['fields' => 'id, name, mimeType'];
        if ($destinationFolderId) {
            $copyParams['fields'] = 'id, name, parents, mimeType';
            $copiedFile->setParents([$destinationFolderId]);
        }

        $convertedFile = $this->getDriveService()->files->copy($wordFileId, $copiedFile, $copyParams);

        return $convertedFile->getId();
    }

    /**
     * Get document content from Google Docs
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getDocumentContent(string $documentId): array
    {
        $document = $this->getDocsService()->documents->get($documentId);

        return [
            'title' => $document->getTitle(),
            'body' => $document->getBody(),
        ];
    }

    /**
     * Replace text in Google Docs document
     *
     * @throws Exception
     */
    public function replaceTextInDocument(string $documentId, string $searchText, string $replaceText): void
    {
        $requests = [
            new Request([
                'replaceAllText' => [
                    'containsText' => [
                        'text' => $searchText,
                        'matchCase' => true,
                    ],
                    'replaceText' => $replaceText,
                ],
            ]),
        ];

        $batchUpdateRequest = new BatchUpdateDocumentRequest([
            'requests' => $requests,
        ]);

        $this->getDocsService()->documents->batchUpdate($documentId, $batchUpdateRequest);
    }

    /**
     * Download document as DOCX
     *
     * @return string File a path
     *
     * @throws Exception
     */
    public function downloadAsDocx(string $fileId, string $savePath): string
    {
        $response = $this->getDriveService()->files->export($fileId, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', [
            'alt' => 'media',
        ]);

        $content = $response->getBody()->getContents();
        file_put_contents($savePath, $content);

        return $savePath;
    }

    /**
     * Download document as PDF
     *
     * @return string File path
     *
     * @throws Exception
     */
    public function downloadAsPdf(string $fileId, string $savePath): string
    {
        $response = $this->getDriveService()->files->export($fileId, 'application/pdf', [
            'alt' => 'media',
        ]);

        $content = $response->getBody()->getContents();
        file_put_contents($savePath, $content);

        return $savePath;
    }

    /**
     * Get file metadata
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function getFileMetadata(string $fileId): array
    {
        $file = $this->getDriveService()->files->get($fileId, ['fields' => 'id, name, mimeType, modifiedTime, createdTime, webViewLink']);

        return [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'mime_type' => $file->getMimeType(),
            'modified_time' => $file->getModifiedTime(),
            'created_time' => $file->getCreatedTime(),
            'web_view_link' => $file->getWebViewLink(),
        ];
    }

    /**
     * Delete a file from Google Drive
     */
    public function deleteFile(string $fileId): void
    {
        try {
            $this->getDriveService()->files->delete($fileId);
        } catch (Exception $e) {
            Log::channel('google')->error('Error deleting file from Google Drive', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Error deleting file from Google Drive: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if a client is authenticated
     */
    public function isAuthenticated(): bool
    {
        if ($this->getClient()->isAccessTokenExpired()) {
            $refreshToken = $this->getRefreshToken();
            if ($refreshToken) {
                try {
                    $accessToken = $this->getClient()->refreshToken($refreshToken);

                    return ! isset($accessToken['error']);
                } catch (\Exception $e) {
                    Log::channel('google')->error('Error refreshing access token', [
                        'error' => $e->getMessage(),
                    ]);

                    return false;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Initialize Google API services
     */
    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $client = $this->getClient();
        $this->driveService = new Drive($client);
        $this->docsService = new Docs($client);
        $this->initialized = true;
    }

    /**
     * Get Client instance (lazy initialization)
     */
    private function getClient(): Client
    {
        if (! $this->client instanceof Client) {
            $this->initializeClient();
        }

        return $this->client;
    }

    /**
     * Get Drive service instance (lazy initialization)
     */
    private function getDriveService(): Drive
    {
        if (! $this->driveService instanceof Drive) {
            $this->initialize();
        }

        return $this->driveService;
    }

    /**
     * Get Docs service instance (lazy initialization)
     */
    private function getDocsService(): Docs
    {
        if (! $this->docsService instanceof Docs) {
            $this->initialize();
        }

        return $this->docsService;
    }

    /**
     * Initialize Google AI client with credentials
     */
    private function initializeClient(): void
    {
        if ($this->client instanceof Client) {
            return;
        }

        $this->client = new Client;
        $this->client->setApplicationName(config('app.name', 'Laravel'));

        // Only set credentials if they are configured (allows for mocking in tests)
        if (config('services.google.client_id')) {
            $this->client->setClientId(config('services.google.client_id'));
        }

        if (config('services.google.client_secret')) {
            $this->client->setClientSecret(config('services.google.client_secret'));
        }

        if (config('services.google.redirect')) {
            $this->client->setRedirectUri(config('services.google.redirect'));
        }

        $this->client->setScopes(config('services.google.scopes', []));
        $this->client->setAccessType(config('services.google.access_type', 'offline'));
        $this->client->setApprovalPrompt(config('services.google.approval_prompt', 'force'));

        $refreshToken = $this->getRefreshToken();

        if ($refreshToken) {
            $this->client->refreshToken($refreshToken);
        }
    }

    /**
     * Get or set refresh token
     */
    private function getRefreshToken(): ?string
    {
        $tokenPath = storage_path('app/google-drive-token.json');
        if (file_exists($tokenPath)) {
            $token = json_decode(file_get_contents($tokenPath), true);

            return $token['refresh_token'] ?? null;
        }

        return null;
    }
}
