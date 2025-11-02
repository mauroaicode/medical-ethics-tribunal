<?php

declare(strict_types=1);

namespace Src\Application\Admin\City\Resources;

use Spatie\LaravelData\Resource;
use Src\Domain\City\Models\City;

class CityResource extends Resource
{
    public function __construct(
        public int $id,
        public string $codigo,
        public string $descripcion,
        public int $department_id,
    ) {}

    public static function fromModel(City $city): self
    {
        return new self(
            id: $city->id,
            codigo: $city->codigo,
            descripcion: $city->descripcion,
            department_id: $city->iddepartamento,
        );
    }
}
