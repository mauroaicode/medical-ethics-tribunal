<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Data;

use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class VerifyCodeData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, In(['process.update', 'process.delete'])]
        public string $action,

        #[Required, Min(6), Max(6)]
        public string $code,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->setAttributeNames([
            'action' => __('data.action'),
            'code' => __('data.code'),
        ]);
    }
}
