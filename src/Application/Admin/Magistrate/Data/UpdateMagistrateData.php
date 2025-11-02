<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\Magistrate\Models\Magistrate;
use Src\Domain\User\Enums\DocumentType;

class UpdateMagistrateData extends Data
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
    ) {}

    public static function rules(): array
    {
        return [
            'document_type' => ['sometimes', Rule::enum(DocumentType::class)],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $magistrate = request()->route('magistrate');
        $userId = null;
        if ($magistrate instanceof Magistrate && $magistrate->user_id) {
            $userId = $magistrate->user_id;
        }

        if ($validator->getData()['document_number'] ?? null) {
            $validator->addRules([
                'document_number' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('users', 'document_number')->ignore($userId, 'id'),
                ],
            ]);
        }

        if ($validator->getData()['email'] ?? null) {
            $validator->addRules([
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($userId, 'id'),
                ],
            ]);
        }

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
        ]);
    }
}
