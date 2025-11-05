<?php

declare(strict_types=1);

namespace Src\Application\Admin\DocumentType\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\DocumentType\Resources\DocumentTypeResource;
use Src\Application\Admin\DocumentType\Services\DocumentTypeFinderService;

class DocumentTypeController
{
    /**
     * Display a listing of available document types.
     */
    public function index(DocumentTypeFinderService $documentTypeFinderService): Collection
    {
        return $documentTypeFinderService->handle()
            ->map(fn (array $documentType): array => DocumentTypeResource::fromArray($documentType)->toArray());
    }
}
