<?php

declare(strict_types=1);

namespace Src\Application\Admin\DocumentType\Resources;

use Spatie\LaravelData\Resource;

class DocumentTypeResource extends Resource
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}

    /**
     * Create a DocumentTypeResource from an array
     *
     * @param  array{value: string, label: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            label: $data['label'],
        );
    }
}
