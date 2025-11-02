<?php

declare(strict_types=1);

namespace Src\Application\Admin\Magistrate\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Magistrate\Models\Magistrate;

class MagistrateResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public ?array $user,
    ) {}

    public static function fromModel(Magistrate $magistrate): self
    {
        return new self(
            id: $magistrate->id,
            user_id: $magistrate->user_id,
            user: ($magistrate->relationLoaded('user') && $magistrate->user) ? [
                'id' => $magistrate->user->id,
                'name' => $magistrate->user->name,
                'last_name' => $magistrate->user->last_name,
                'email' => $magistrate->user->email,
                'document_type' => $magistrate->user->document_type->getLabel(),
                'document_number' => $magistrate->user->document_number,
                'phone' => $magistrate->user->phone,
                'address' => $magistrate->user->address,
            ] : null,
        );
    }
}
