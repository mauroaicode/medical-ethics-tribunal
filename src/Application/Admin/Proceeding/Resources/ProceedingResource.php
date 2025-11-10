<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Resources;

use Spatie\LaravelData\Resource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Src\Domain\Proceeding\Models\Proceeding;

class ProceedingResource extends Resource
{
    public function __construct(
        public int $id,
        public int $process_id,
        public string $name,
        public string $description,
        public string $proceeding_date,
        public ?array $process = null,
        public ?array $file = null,
    ) {}

    public static function fromModel(Proceeding $proceeding): self
    {
        $media = $proceeding->getFirstMedia($proceeding->getMediaCollectionName());

        return new self(
            id: $proceeding->id,
            process_id: $proceeding->process_id,
            name: $proceeding->name,
            description: $proceeding->description,
            proceeding_date: $proceeding->proceeding_date->format('Y-m-d'),
            process: $proceeding->relationLoaded('process') ? [
                'id' => $proceeding->process->id,
                'name' => $proceeding->process->name,
                'process_number' => $proceeding->process->process_number,
            ] : null,
            file: $media instanceof Media ? [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getUrl(),
                'created_at' => $media->created_at->format('Y-m-d H:i:s'),
            ] : null,
        );
    }
}
