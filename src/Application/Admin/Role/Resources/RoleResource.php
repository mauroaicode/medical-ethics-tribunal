<?php

declare(strict_types=1);

namespace Src\Application\Admin\Role\Resources;

use Spatie\LaravelData\Resource;

class RoleResource extends Resource
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}

    /**
     * Create a RoleResource from an array
     *
     * @param  array{value: string, label: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: $data['value'],
            label: $data['label'],
        );
    }
}
