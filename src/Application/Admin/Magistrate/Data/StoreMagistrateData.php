<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\User\Enums\DocumentType;

class StoreMagistrateData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Min(2), Max(255)]
        public string $name,

        #[Required, Min(2), Max(255)]
        public string $last_name,

        #[Required]
        public DocumentType $document_type,

        #[Required, Max(255), Unique('users', 'document_number')]
        public string $document_number,

        #[Required, Max(255)]
        public string $phone,

        #[Required, Max(500)]
        public string $address,

        #[Required, Email, Unique('users', 'email')]
        public string $email,
    ) {}

    public static function rules(): array
    {
        return [
            'document_type' => ['required', Rule::enum(DocumentType::class)],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'document_type.enum' => __('validation.enum', ['attribute' => __('data.document_type')]),
            'document_type.in' => __('validation.in', ['attribute' => __('data.document_type')]),
        ]);

        $validator->setAttributeNames([
            'name' => __('data.name'),
            'last_name' => __('data.last_name'),
            'document_type' => __('data.document_type'),
            'document_number' => __('data.document_number'),
            'phone' => __('data.phone'),
            'address' => __('data.address'),
            'email' => __('data.email'),
        ]);
    }
}
