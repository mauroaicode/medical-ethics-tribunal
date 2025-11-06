<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Data;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class DeleteProcessData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Min(3), Max(1000)]
        public string $deleted_reason,
    ) {}
}
