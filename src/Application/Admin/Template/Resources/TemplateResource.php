<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Template\Models\Template;

class TemplateResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?string $google_drive_id = null,
        public ?string $google_drive_file_id = null,
    ) {}

    public static function fromModel(Template $template): self
    {
        return new self(
            id: $template->id,
            name: $template->name,
            description: $template->description,
            google_drive_id: $template->google_drive_id,
            google_drive_file_id: $template->google_drive_file_id,
        );
    }
}
