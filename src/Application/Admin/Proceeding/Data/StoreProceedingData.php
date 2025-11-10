<?php

declare(strict_types=1);

namespace Src\Application\Admin\Proceeding\Data;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class StoreProceedingData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Exists('processes', 'id')]
        public int $process_id,

        #[Required, Min(2), Max(255)]
        public string $name,

        #[Required, Min(2), Max(1000)]
        public string $description,

        #[Required, Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon $proceeding_date,

        #[Required]
        public UploadedFile $file,
    ) {}

    public static function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            'proceeding_date' => ['required', 'date'],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'file.required' => __('validation.required', ['attribute' => __('data.file')]),
            'file.file' => __('validation.file', ['attribute' => __('data.file')]),
            'file.mimes' => __('validation.mimes', ['attribute' => __('data.file'), 'values' => 'pdf']),
            'file.max' => __('validation.max.file', ['attribute' => __('data.file'), 'max' => '10MB']),
            'proceeding_date.required' => __('validation.required', ['attribute' => __('data.proceeding_date')]),
            'proceeding_date.date' => __('validation.date', ['attribute' => __('data.proceeding_date')]),
        ]);
    }
}
