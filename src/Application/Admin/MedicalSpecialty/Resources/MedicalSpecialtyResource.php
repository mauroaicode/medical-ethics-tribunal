<?php

declare(strict_types=1);

namespace Src\Application\Admin\MedicalSpecialty\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\MedicalSpecialty\Models\MedicalSpecialty;

class MedicalSpecialtyResource extends Resource
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
    ) {}

    public static function fromModel(MedicalSpecialty $specialty): self
    {
        return new self(
            id: $specialty->id,
            name: $specialty->name,
            description: $specialty->description,
        );
    }
}
