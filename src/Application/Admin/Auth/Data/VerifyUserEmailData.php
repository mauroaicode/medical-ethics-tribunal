<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class VerifyUserEmailData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[FromRouteParameter('id')]
        public readonly int $id,

        #[FromRouteParameter('hash')]
        public readonly string $hash,
    ) {
    }

    /**
     * Specifies attributes to be excluded from translation.
     *
     * @return array<string> A list of attribute names to exclude from translation.
     */
    protected static function excludedAttributesFromTranslation(): array
    {
        return ['id', 'hash'];
    }
}

