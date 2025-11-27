<?php

declare(strict_types=1);

namespace Src\Application\Admin\ProcessTemplateDocument\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\ProcessTemplateDocument\Models\ProcessTemplateDocument;
use Src\Domain\Template\Models\Template;

class ProcessTemplateDocumentResource extends Resource
{
    public function __construct(
        public int $id,
        public int $process_id,
        public int $template_id,
        public string $file_name,
        public ?string $google_drive_file_id,
        public ?string $google_docs_name,
        public ?string $document_url,
        public ?array $template = null,
    ) {}

    public static function fromModel(ProcessTemplateDocument $processTemplateDocument): self
    {
        $media = $processTemplateDocument->relationLoaded('media') && $processTemplateDocument->media->isNotEmpty()
            ? $processTemplateDocument->media->first()
            : $processTemplateDocument->getFirstMedia($processTemplateDocument->getMediaCollectionName());

        $template = null;
        if ($processTemplateDocument->relationLoaded('template')) {
            /** @var Template|null $loadedTemplate */
            $loadedTemplate = $processTemplateDocument->template;
            if ($loadedTemplate !== null) {
                $template = [
                    'id' => $loadedTemplate->id,
                    'name' => $loadedTemplate->name,
                    'description' => $loadedTemplate->description,
                ];
            }
        }

        return new self(
            id: $processTemplateDocument->id,
            process_id: $processTemplateDocument->process_id,
            template_id: $processTemplateDocument->template_id,
            file_name: $processTemplateDocument->file_name,
            google_drive_file_id: $processTemplateDocument->google_drive_file_id,
            google_docs_name: $processTemplateDocument->google_docs_name,
            document_url: $media?->getUrl(),
            template: $template,
        );
    }
}
