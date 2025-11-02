<?php

declare(strict_types=1);

namespace Src\Application\Admin\Session\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Session\Models\Session;

class SessionResource extends Resource
{
    public function __construct(
        public string $id,
        public ?int $user_id,
        public ?array $user,
        public ?string $ip_address,
        public ?string $user_agent,
        public ?string $location,
        public int $last_activity,
    ) {}

    public static function fromModel(Session $session): self
    {
        return new self(
            id: $session->id,
            user_id: $session->user_id,
            user: $session->user ? [
                'id' => $session->user->id,
                'name' => $session->user->name,
                'last_name' => $session->user->last_name,
                'email' => $session->user->email,
            ] : null,
            ip_address: $session->ip_address,
            user_agent: $session->user_agent,
            location: $session->location,
            last_activity: $session->last_activity,
        );
    }
}
