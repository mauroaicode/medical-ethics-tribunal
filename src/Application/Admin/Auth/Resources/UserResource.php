<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\User\Enums\UserRole;
use Src\Domain\User\Models\User;

class UserResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public string $last_name,
        public string $email,
        public ?string $document_type = null,
        public ?string $document_number = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $status = null,
        public ?array $roles = null,
    ) {}

    public static function fromModel(User $user): self
    {
        $roles = $user->roles->map(function ($role) {
            return [
                'value' => $role->name,
                'label' => UserRole::getLabelFor($role->name) ?? $role->name,
            ];
        })->toArray();

        return new self(
            id: $user->id,
            name: $user->name,
            last_name: $user->last_name,
            email: $user->email,
            document_type: $user->document_type?->getLabel(),
            document_number: $user->document_number,
            phone: $user->phone,
            address: $user->address,
            status: $user->status?->getLabel(),
            roles: $roles,
        );
    }
}
