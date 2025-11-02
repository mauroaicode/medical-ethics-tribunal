<?php

declare(strict_types=1);

namespace Src\Application\Shared\Traits;

use Illuminate\Support\Str;

trait GeneratesPasswordTrait
{
    /**
     * Generate a secure random password
     */
    private function generateSecurePassword(): string
    {
        $length = config()->integer('auth.temporary_password_length', 8);

        return Str::random($length);
    }
}
