<?php

declare(strict_types=1);

namespace Src\Domain\Shared\Traits;

use Spatie\MediaLibrary\InteractsWithMedia as SpatieInteractsWithMedia;
use Src\Domain\Shared\Enums\FileType;

trait InteractsWithCustomMedia
{
    use SpatieInteractsWithMedia;

    /**
     * Get the media collection name for the model.
     * Override this method in each model to return the appropriate FileType value.
     */
    public function getMediaCollectionName(): string
    {
        return FileType::PROFILE_IMAGE->value;
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->getMediaCollectionName())
            ->singleFile();
    }
}
