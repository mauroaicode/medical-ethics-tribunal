<?php

declare(strict_types=1);

namespace Src\Application\Admin\Template\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class AssignTemplateToProcessData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Exists('templates', 'id')]
        public int $template_id,

        #[Required, Exists('processes', 'id')]
        public int $process_id,
    ) {}
}
