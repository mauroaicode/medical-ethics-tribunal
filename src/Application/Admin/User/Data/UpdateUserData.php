<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Application\Shared\Traits\ValidatesRolesTrait;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Enums\UserStatus;

class UpdateUserData extends Data
{
    use TranslatableDataAttributesTrait;
    use ValidatesRolesTrait;

    public function __construct(
        #[Max(255),
            Unique(
                table: 'users',
                column: 'document_number',
                ignore: new RouteParameterReference(
                    routeParameter: 'user',
                    property: 'id'
                )
            )]
        public ?string $document_number = null,

        #[Max(255)]
        public ?string $phone = null,

        #[Max(500)]
        public ?string $address = null,

        #[Email,
            Unique(
                table: 'users',
                column: 'email',
                ignore: new RouteParameterReference(
                    routeParameter: 'user',
                    property: 'id'
                )
            )]
        public ?string $email = null,

        #[Password(
            min: 12,
            letters: true,
            mixedCase: true,
            numbers: true,
            symbols: true
        )]
        public ?string $password = null,

        #[Min(2), Max(255)]
        public ?string $name = null,

        #[Min(2), Max(255)]
        public ?string $last_name = null,

        public ?DocumentType $document_type = null,

        public ?array $roles = null,

        public ?UserStatus $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'document_type' => ['sometimes', Rule::enum(DocumentType::class)],
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(UserRole::values())],
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
            'roles' => __('data.roles'),
            'status' => __('data.status'),
        ]);

        static::validateRoles($validator, requireRoles: false);
    }
}
