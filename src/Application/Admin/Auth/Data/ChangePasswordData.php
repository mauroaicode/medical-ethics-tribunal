<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class ChangePasswordData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Required,
            Password(
                min: 12,
                letters: true,
                mixedCase: true,
                numbers: true,
                symbols: true
            ),
            Confirmed]
        public string $password,

        #[Required]
        public string $password_confirmation,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->setCustomMessages([
            'password.letters' => __('validation.password.letters', ['attribute' => __('data.password')]),
            'password.mixed' => __('validation.password.mixed', ['attribute' => __('data.password')]),
            'password.numbers' => __('validation.password.numbers', ['attribute' => __('data.password')]),
            'password.symbols' => __('validation.password.symbols', ['attribute' => __('data.password')]),
            'password_confirmation.confirmed' => __('validation.confirmed', ['attribute' => __('data.password_confirmation')]),
        ]);

        $validator->setAttributeNames([
            'password' => __('data.password'),
            'password_confirmation' => __('data.password_confirmation'),
        ]);
    }
}
