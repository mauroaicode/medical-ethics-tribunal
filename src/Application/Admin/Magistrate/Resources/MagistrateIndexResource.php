<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Magistrate\Models\Magistrate;

class MagistrateIndexResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public string $last_name,
        public string $email,
        public ?string $phone,
        public string $created_at,
    ) {}

    public static function fromModel(Magistrate $magistrate): self
    {
        return new self(
            id: $magistrate->id,
            user_id: $magistrate->user_id,
            name: $magistrate->relationLoaded('user') && $magistrate->user ? $magistrate->user->name : '',
            last_name: $magistrate->relationLoaded('user') && $magistrate->user ? $magistrate->user->last_name : '',
            email: $magistrate->relationLoaded('user') && $magistrate->user ? $magistrate->user->email : '',
            phone: $magistrate->relationLoaded('user') && $magistrate->user ? $magistrate->user->phone : null,
            created_at: $magistrate->created_at->format('Y-m-d'),
        );
    }
}
