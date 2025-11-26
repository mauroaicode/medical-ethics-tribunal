<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Data;

use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class TemplateFilterData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        public ?string $name = null,
    ) {}
}
