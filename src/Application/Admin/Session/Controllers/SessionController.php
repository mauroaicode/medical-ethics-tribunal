<?php

declare(strict_types=1);

namespace Src\Application\Admin\Session\Controllers;

use Illuminate\Support\Collection;
use Src\Application\Admin\Session\Resources\SessionResource;
use Src\Application\Admin\Session\Services\SessionFinderService;
use Src\Domain\Session\Models\Session;

class SessionController
{
    /**
     * Display a listing of sessions.
     */
    public function index(SessionFinderService $sessionFinderService): Collection
    {
        return $sessionFinderService->handle()
            ->map(fn (Session $session): array => SessionResource::fromModel($session)->toArray());
    }
}
