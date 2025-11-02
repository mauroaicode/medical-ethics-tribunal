<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Illuminate\Validation\Validator;
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

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'password.letters' => __('validation.password.letters', ['attribute' => __('data.password')]),
            'password.mixed' => __('validation.password.mixed', ['attribute' => __('data.password')]),
            'password.numbers' => __('validation.password.numbers', ['attribute' => __('data.password')]),
            'password.symbols' => __('validation.password.symbols', ['attribute' => __('data.password')]),
            'password_confirmation.letters' => __('validation.password.letters', ['attribute' => __('data.password_confirmation')]),
            'password_confirmation.mixed' => __('validation.password.mixed', ['attribute' => __('data.password_confirmation')]),
            'password_confirmation.numbers' => __('validation.password.numbers', ['attribute' => __('data.password_confirmation')]),
            'password_confirmation.symbols' => __('validation.password.symbols', ['attribute' => __('data.password_confirmation')]),
        ]);

        $validator->setAttributeNames([
            'password' => __('data.password'),
            'password_confirmation' => __('data.password_confirmation'),
        ]);
    }
}
