<?php

declare(strict_types=1);

namespace Src\Application\Admin\Process\Data;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\Process\Enums\ProcessStatus;

class ProcessFilterData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        public ?string $process_number = null,
        public ?string $complainant_document_number = null,
        public ?string $doctor_name = null,
        #[Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $start_date = null,
        #[Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $start_date_from = null,
        #[Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $start_date_to = null,
        public ?ProcessStatus $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'status' => ['sometimes', 'nullable', Rule::enum(ProcessStatus::class)],
        ];
    }
}
