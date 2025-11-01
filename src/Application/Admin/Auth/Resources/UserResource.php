<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\User\Models\User;

class UserResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public string $last_name,
        public string $email,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            last_name: $user->last_name,
            email: $user->email,
        );
    }
}

