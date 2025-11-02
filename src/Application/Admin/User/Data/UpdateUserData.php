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
            'roles' => ['sometimes', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(UserRole::values())],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        static::validateRoles($validator, requireRoles: false);
    }
}
