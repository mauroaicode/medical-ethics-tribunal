<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Src\Application\Shared\Traits\TranslatableDataAttributesTrait;

class VerifyResetCodeData extends Data
{
    use TranslatableDataAttributesTrait;

    #[Required, Email, Exists('users', 'email')]
    public string $email;

    #[Required, Min(6)]
    public int $code;
}

