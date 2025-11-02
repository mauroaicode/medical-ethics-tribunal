<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Data;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\Doctor\Models\Doctor;
use Src\Domain\User\Enums\DocumentType;

class UpdateDoctorData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Min(2), Max(255)]
        public ?string $name = null,

        #[Min(2), Max(255)]
        public ?string $last_name = null,

        public ?DocumentType $document_type = null,

        #[Max(255)]
        public ?string $document_number = null,

        #[Max(255)]
        public ?string $phone = null,

        #[Max(500)]
        public ?string $address = null,

        #[Email]
        public ?string $email = null,

        #[Password(
            min: 12,
            letters: true,
            mixedCase: true,
            numbers: true,
            symbols: true
        )]
        public ?string $password = null,

        #[Exists('medical_specialties', 'id')]
        public ?int $specialty_id = null,

        #[Max(255)]
        public ?string $faculty = null,

        #[Max(255),
            Unique(
                table: 'doctors',
                column: 'medical_registration_number',
                ignore: new RouteParameterReference(
                    routeParameter: 'doctor',
                    property: 'id'
                )
            )]
        public ?string $medical_registration_number = null,

        #[Max(255)]
        public ?string $medical_registration_place = null,

        #[Date, WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public ?Carbon $medical_registration_date = null,

        #[Max(255)]
        public ?string $main_practice_company = null,

        #[Max(255)]
        public ?string $other_practice_company = null,
    ) {}

    public static function rules(): array
    {
        return [
            'document_type' => ['sometimes', Rule::enum(DocumentType::class)],
            'document_number' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('users', 'document_number')->ignore(
                    function () {
                        $doctor = request()->route('doctor');
                        if ($doctor instanceof Doctor && $doctor->user_id) {
                            return $doctor->user_id;
                        }

                        return null;
                    },
                    'id'
                ),
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(
                    function () {
                        $doctor = request()->route('doctor');
                        if ($doctor instanceof Doctor && $doctor->user_id) {
                            return $doctor->user_id;
                        }

                        return null;
                    },
                    'id'
                ),
            ],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'document_type.enum' => __('validation.enum', ['attribute' => __('data.document_type')]),
            'document_type.in' => __('validation.in', ['attribute' => __('data.document_type')]),
            'password.letters' => __('validation.password.letters', ['attribute' => __('data.password')]),
            'password.mixed' => __('validation.password.mixed', ['attribute' => __('data.password')]),
            'password.numbers' => __('validation.password.numbers', ['attribute' => __('data.password')]),
            'password.symbols' => __('validation.password.symbols', ['attribute' => __('data.password')]),
        ]);

        $validator->setAttributeNames([
            'name' => __('data.name'),
            'last_name' => __('data.last_name'),
            'document_type' => __('data.document_type'),
            'document_number' => __('data.document_number'),
            'phone' => __('data.phone'),
            'address' => __('data.address'),
            'email' => __('data.email'),
            'password' => __('data.password'),
            'specialty_id' => __('data.specialty_id'),
            'faculty' => __('data.faculty'),
            'medical_registration_number' => __('data.medical_registration_number'),
            'medical_registration_place' => __('data.medical_registration_place'),
            'medical_registration_date' => __('data.medical_registration_date'),
            'main_practice_company' => __('data.main_practice_company'),
            'other_practice_company' => __('data.other_practice_company'),
        ]);
    }
}
