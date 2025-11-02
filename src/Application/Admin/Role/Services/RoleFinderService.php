<?php

declare(strict_types=1);

namespace Src\Application\Admin\Role\Services;

use Illuminate\Support\Collection;
use Src\Domain\User\Enums\UserRole;

class RoleFinderService
{
    /**
     * Get all available roles
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public function handle(): Collection
    {
        return collect(UserRole::cases())
            ->map(fn (UserRole $role): array => [
                'value' => $role->value,
                'label' => $role->getLabel(),
            ]);
    }
}
