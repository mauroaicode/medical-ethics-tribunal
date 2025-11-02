<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Same;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class ResetPasswordData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required, Min(6)]
        public int $code,

        #[Email, Exists('users', 'email')]
        public readonly string $email,

        #[Required, Password(
            min: 12,
            letters: true,
            mixedCase: true,
            numbers: true,
            symbols: true
        )]
        public readonly string $password,

        #[Required,
            Password(
                min: 12,
                letters: true,
                mixedCase: true,
                numbers: true,
                symbols: true
            ),
            Same('password')]
        public readonly string $password_confirmation,
    ) {}
}
