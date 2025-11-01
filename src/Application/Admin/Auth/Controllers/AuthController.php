<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Src\Application\Admin\Auth\Data\LoginData;
use Src\Application\Admin\Auth\Resources\AuthResource;
use Src\Domain\User\Models\User;

class AuthController
{
    /**
     * Authenticate user and generate token
     */
    public function login(LoginData $data): Response|JsonResponse
    {
        $user = User::query()->where('email', $data->email)->first();

        $token = $user->createToken('auth-token')->plainTextToken;

        $user->update([
            'last_login_ip' => request()->ip(),
            'last_login_at' => now(),
        ]);

        return response(AuthResource::fromModel($user, $token)->toArray(), 200);
    }
}

