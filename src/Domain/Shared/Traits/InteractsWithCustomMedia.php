<?php

declare(strict_types=1);

namespace Src\Domain\Shared\Traits;

use Src\Domain\Shared\Enums\FileType;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia as SpatieInteractsWithMedia;

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

