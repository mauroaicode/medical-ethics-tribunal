<?php

declare(strict_types=1);

namespace Src\Application\Admin\User\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Src\Application\Admin\Auth\Resources\UserResource;
use Src\Application\Admin\User\Data\StoreUserData;
use Src\Application\Admin\User\Data\UpdateUserData;
use Src\Application\Admin\User\Services\UserCreatorService;
use Src\Application\Admin\User\Services\UserDeleterService;
use Src\Application\Admin\User\Services\UserFinderService;
use Src\Application\Admin\User\Services\UserUpdaterService;
use Src\Domain\User\Models\User;
use Throwable;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserFinderService $userFinderService): Collection
    {
        return $userFinderService->handle()
            ->map(fn (User $user): array => UserResource::fromModel($user)->toArray());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): array
    {
        $user->load('roles');

        return UserResource::fromModel($user)->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(UserCreatorService $userCreatorService, StoreUserData $storeUserData): Response
    {
        $user = $userCreatorService->handle($storeUserData);

        return response(UserResource::fromModel($user)->toArray(), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws Throwable
     */
    public function update(UpdateUserData $updateUserData, User $user): Response
    {
        $updatedUser = (new UserUpdaterService)->handle($updateUserData, $user);

        return response(UserResource::fromModel($updatedUser)->toArray(), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws Throwable
     */
    public function destroy(User $user): Response
    {
        (new UserDeleterService)->handle($user);

        return new Response(status: 204);
    }
}
