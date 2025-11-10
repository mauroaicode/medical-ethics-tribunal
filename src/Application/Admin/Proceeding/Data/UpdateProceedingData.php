<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Data;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class UpdateProceedingData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Nullable, Min(2), Max(255)]
        public readonly ?string $name = null,

        #[Nullable, Min(2), Max(1000)]
        public readonly ?string $description = null,

        #[Nullable, Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public readonly ?Carbon $proceeding_date = null,

        #[WithoutValidation]
        public readonly ?UploadedFile $file = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'min:2', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'min:2', 'max:1000'],
            'proceeding_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $data = $validator->getData();

        if (isset($data['file'])) {
            $validator->addRules([
                'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            ]);
        }

        $validator->setCustomMessages([
            'file.required' => __('validation.required', ['attribute' => __('data.file')]),
            'file.file' => __('validation.file', ['attribute' => __('data.file')]),
            'file.mimes' => __('validation.mimes', ['attribute' => __('data.file'), 'values' => 'pdf']),
            'file.max' => __('validation.max.file', ['attribute' => __('data.file'), 'max' => '10MB']),
            'proceeding_date.date' => __('validation.date', ['attribute' => __('data.proceeding_date')]),
        ]);
    }
}
