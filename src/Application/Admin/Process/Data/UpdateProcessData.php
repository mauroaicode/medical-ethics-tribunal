<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Data;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\Process\Enums\ProcessStatus;

class UpdateProcessData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Exists('complainants', 'id')]
        public ?int $complainant_id = null,

        #[Exists('doctors', 'id')]
        public ?int $doctor_id = null,

        #[Exists('magistrates', 'id')]
        public ?int $magistrate_instructor_id = null,

        #[Exists('magistrates', 'id')]
        public ?int $magistrate_ponente_id = null,

        #[Min(2), Max(255)]
        public ?string $name = null,

        #[Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $start_date = null,

        public ?ProcessStatus $status = null,

        #[Min(2)]
        public ?string $description = null,
    ) {}

    public static function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(ProcessStatus::class)],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'status.enum' => __('validation.enum', ['attribute' => __('data.status')]),
            'status.in' => __('validation.in', ['attribute' => __('data.status')]),
        ]);

        $validator->setAttributeNames([
            'complainant_id' => __('data.complainant_id'),
            'doctor_id' => __('data.doctor_id'),
            'magistrate_instructor_id' => __('data.magistrate_instructor_id'),
            'magistrate_ponente_id' => __('data.magistrate_ponente_id'),
            'name' => __('data.name'),
            'start_date' => __('data.start_date'),
            'status' => __('data.status'),
            'description' => __('data.description'),
        ]);
    }
}
