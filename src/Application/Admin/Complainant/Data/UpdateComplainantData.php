<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Domain\Complainant\Models\Complainant;
use Src\Domain\User\Enums\DocumentType;

class UpdateComplainantData extends Data
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

        #[Exists('cities', 'id')]
        public ?int $city_id = null,

        #[Max(255)]
        public ?string $municipality = null,

        #[Max(255)]
        public ?string $company = null,

        public ?bool $is_anonymous = null,
    ) {}

    public static function rules(): array
    {
        return [
            'document_type' => ['sometimes', Rule::enum(DocumentType::class)],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        $complainant = request()->route('complainant');
        $userId = null;
        if ($complainant instanceof Complainant && $complainant->user_id) {
            $userId = $complainant->user_id;
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
        ]);

        $validator->setAttributeNames([
            'name' => __('data.name'),
            'last_name' => __('data.last_name'),
            'document_type' => __('data.document_type'),
            'document_number' => __('data.document_number'),
            'phone' => __('data.phone'),
            'address' => __('data.address'),
            'email' => __('data.email'),
            'city_id' => __('data.city_id'),
            'municipality' => __('data.municipality'),
            'company' => __('data.company'),
            'is_anonymous' => __('data.is_anonymous'),
        ]);
    }
}
