<?php

declare(strict_types=1);

namespace Src\Application\Shared\PathGenerators;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ProceedingDocumentPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, relative to the root storage path.
     * Uses a hash of the model ID to prevent predictable paths.
     */
    public function getPath(Media $media): string
    {
        $hashedId = md5($media->model_id.config('app.key'));

        return "proceeding_documents/{$hashedId}/";
    }

    /**
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'conversions/';
    }

    /**
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'responsive-images/';
    }
}
