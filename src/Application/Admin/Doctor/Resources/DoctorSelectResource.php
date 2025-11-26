<?php

declare(strict_types=1);

namespace Src\Application\Admin\Doctor\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\Doctor\Models\Doctor;

class DoctorSelectResource extends Resource
{
    public function __construct(
        public int $id,
        public string $full_name,
    ) {}

    public static function fromModel(Doctor $doctor): self
    {
        $user = $doctor->user;
        $fullName = $user
            ? trim("{$user->name} {$user->last_name}")
            : '';

        return new self(
            id: $doctor->id,
            full_name: $fullName,
        );
    }
}
