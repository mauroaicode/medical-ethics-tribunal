<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\User\Models\User;

class AuthResource extends Resource
{
    public function __construct(
        public string $token,
        public UserResource $user,
    ) {}

    public static function fromModel(User $user, string $token): self
    {
        return new self(
            token: $token,
            user: UserResource::fromModel($user),
        );
    }
}
