<?php

declare(strict_types=1);

namespace Src\Application\Admin\Role\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\Role\Resources\RoleResource;
use Src\Application\Admin\Role\Services\RoleFinderService;

class RoleController
{
    /**
     * Display a listing of available roles.
     */
    public function index(): Collection
    {
        return (new RoleFinderService)->handle()
            ->map(fn (array $role): array => RoleResource::fromArray($role)->toArray());
    }
}
