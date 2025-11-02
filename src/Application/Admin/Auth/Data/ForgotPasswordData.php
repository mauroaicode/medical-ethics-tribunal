<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class ForgotPasswordData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Email, Exists('users', 'email')]
        public readonly string $email,
    ) {}

    public static function withValidator(Validator $validator): void
    {
        $validator->setAttributeNames([
            'email' => __('data.email'),
        ]);
    }
}
