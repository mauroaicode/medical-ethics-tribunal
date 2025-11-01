<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class ForgotPasswordData extends Data
{
    use TranslatableDataAttributesTrait;

    public function __construct(
        #[Email, Exists('users', 'email')]
        public readonly string $email,
    ) {
    }
}

