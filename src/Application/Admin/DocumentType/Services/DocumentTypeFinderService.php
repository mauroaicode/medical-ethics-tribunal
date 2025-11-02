<?php

declare(strict_types=1);

namespace Src\Application\Admin\DocumentType\Services;

use Illuminate\Support\Collection;
use Src\Domain\User\Enums\DocumentType;

class DocumentTypeFinderService
{
    /**
     * Get all available document types
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public function handle(): Collection
    {
        return collect(DocumentType::cases())
            ->map(fn (DocumentType $documentType): array => [
                'value' => $documentType->value,
                'label' => $documentType->getLabel(),
            ]);
    }
}
