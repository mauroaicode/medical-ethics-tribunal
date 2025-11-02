<?php

declare(strict_types=1);

namespace Src\Application\Admin\Complainant\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Complainant\Models\Complainant;

class ComplainantResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public int $city_id,
        public ?string $municipality = null,
        public ?string $company = null,
        public bool $is_anonymous = false,
        public ?array $user = null,
        public ?array $city = null,
    ) {}

    public static function fromModel(Complainant $complainant): self
    {
        return new self(
            id: $complainant->id,
            user_id: $complainant->user_id,
            city_id: $complainant->city_id,
            municipality: $complainant->municipality,
            company: $complainant->company,
            is_anonymous: $complainant->is_anonymous,
            user: ($complainant->relationLoaded('user') && $complainant->user) ? [
                'id' => $complainant->user->id,
                'name' => $complainant->user->name,
                'last_name' => $complainant->user->last_name,
                'email' => $complainant->user->email,
                'document_type' => $complainant->user->document_type->getLabel(),
                'document_number' => $complainant->user->document_number,
                'phone' => $complainant->user->phone,
                'address' => $complainant->user->address,
            ] : null,
            city: ($complainant->relationLoaded('city') && $complainant->city) ? [
                'id' => $complainant->city->id,
                'name' => $complainant->city->descripcion,
            ] : null,
        );
    }
}
