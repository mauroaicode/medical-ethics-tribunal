<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Doctor\Models\Doctor;

class DoctorIndexResource extends Resource
{
    public function __construct(
        public int $id,
        public int $user_id,
        public string $name,
        public string $last_name,
        public string $email,
        public string $specialty,
        public ?string $phone,
        public ?string $main_practice_company,
        public string $created_at,
    ) {}

    public static function fromModel(Doctor $doctor): self
    {
        return new self(
            id: $doctor->id,
            user_id: $doctor->user_id,
            name: $doctor->relationLoaded('user') && $doctor->user ? $doctor->user->name : '',
            last_name: $doctor->relationLoaded('user') && $doctor->user ? $doctor->user->last_name : '',
            email: $doctor->relationLoaded('user') && $doctor->user ? $doctor->user->email : '',
            specialty: $doctor->relationLoaded('specialty') && $doctor->specialty ? $doctor->specialty->name : '',
            phone: $doctor->relationLoaded('user') && $doctor->user ? $doctor->user->phone : null,
            main_practice_company: $doctor->main_practice_company,
            created_at: $doctor->created_at->format('Y-m-d'),
        );
    }
}
