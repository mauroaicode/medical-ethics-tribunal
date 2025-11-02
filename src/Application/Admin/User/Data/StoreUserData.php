<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Data;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;
use Src\Application\Shared\Traits\ValidatesRolesTrait;
use Src\Domain\User\Enums\DocumentType;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Enums\UserStatus;

class StoreUserData extends Data
{
    use TranslatableDataAttributesTrait, ValidatesRolesTrait;

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

        #[Required, Password(
            min: 12,
            letters: true,
            mixedCase: true,
            numbers: true,
            symbols: true
        )]
        public string $password,

        #[Required]
        public array $roles,

        public ?UserStatus $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', Rule::in(UserRole::values())],
        ];
    }

    public static function withValidator(Validator $validator): void
    {
        static::validateRoles($validator, requireRoles: true);
    }
}
