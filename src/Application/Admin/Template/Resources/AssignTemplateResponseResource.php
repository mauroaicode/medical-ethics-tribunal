<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Resources;

use Spatie\LaravelData\Resource;

class AssignTemplateResponseResource extends Resource
{
    public function __construct(
        public string $message,
        /** @var array<string, mixed> */
        public array $document,
    ) {}

    public static function fromDocument(string $message, array $document): self
    {
        return new self(
            message: $message,
            document: $document,
        );
    }
}
