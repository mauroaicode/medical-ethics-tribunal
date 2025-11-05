<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Template\Models\Template;

class SyncTemplatesResponseResource extends Resource
{
    public function __construct(
        public string $message,
        /** @var array<int, array<string, mixed>> */
        public array $templates,
    ) {}

    public static function fromTemplates(string $message, array $templates): self
    {
        return new self(
            message: $message,
            templates: collect($templates)->map(
                fn (Template $template): array => TemplateResource::fromModel($template)->toArray()
            )->all(),
        );
    }
}
