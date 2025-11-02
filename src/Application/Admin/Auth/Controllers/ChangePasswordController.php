<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Controllers;

use Illuminate\Http\Response;
use Src\Application\Admin\Auth\Data\ChangePasswordData;
use Src\Application\Admin\Auth\Resources\UserResource;
use Src\Application\Admin\Auth\Services\ChangePasswordService;
use Src\Domain\User\Models\User;
use Throwable;

class ChangePasswordController
{
    /**
     * Change authenticated user password
     *
     * @throws Throwable
     */
    public function __invoke(ChangePasswordData $changePasswordData): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $updatedUser = (new ChangePasswordService)->handle($changePasswordData, $user);

        return response(UserResource::fromModel($updatedUser)->toArray(), 200);
    }
}
