<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Data;

use Carbon\Carbon;
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

class StoreProcessData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Exists('complainants', 'id')]
        public int $complainant_id,

        #[Required, Exists('doctors', 'id')]
        public int $doctor_id,

        #[Required, Exists('magistrates', 'id')]
        public int $magistrate_instructor_id,

        #[Required, Exists('magistrates', 'id')]
        public int $magistrate_ponente_id,

        #[Required, Min(2), Max(255)]
        public string $name,

        #[Required, Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public Carbon $start_date,

        #[Required, Min(2)]
        public string $description,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->setAttributeNames([
            'complainant_id' => __('data.complainant_id'),
            'doctor_id' => __('data.doctor_id'),
            'magistrate_instructor_id' => __('data.magistrate_instructor_id'),
            'magistrate_ponente_id' => __('data.magistrate_ponente_id'),
            'name' => __('data.name'),
            'start_date' => __('data.start_date'),
            'description' => __('data.description'),
        ]);
    }
}
