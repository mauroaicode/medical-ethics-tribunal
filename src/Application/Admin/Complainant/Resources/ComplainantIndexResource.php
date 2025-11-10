<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Complainant\Models\Complainant;

class ComplainantIndexResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public string $last_name,
        public string $email,
        public ?string $location,
        public bool $is_anonymous,
        public string $created_at,
    ) {}

    public static function fromModel(Complainant $complainant): self
    {
        $location = null;

        // Si hay municipality, usar municipality, sino usar el nombre de la ciudad
        if ($complainant->municipality) {
            $location = $complainant->municipality;
        } elseif ($complainant->relationLoaded('city') && $complainant->city) {
            $location = $complainant->city->descripcion;
        }

        return new self(
            id: $complainant->id,
            user_id: $complainant->user_id,
            name: $complainant->relationLoaded('user') && $complainant->user ? $complainant->user->name : '',
            last_name: $complainant->relationLoaded('user') && $complainant->user ? $complainant->user->last_name : '',
            email: $complainant->relationLoaded('user') && $complainant->user ? $complainant->user->email : '',
            location: $location,
            is_anonymous: $complainant->is_anonymous,
            created_at: $complainant->created_at->format('Y-m-d'),
        );
    }
}
