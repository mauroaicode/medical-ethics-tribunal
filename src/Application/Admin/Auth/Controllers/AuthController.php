<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Src\Application\Admin\Auth\Data\LoginData;
use Src\Application\Admin\Auth\Resources\AuthResource;
use Src\Application\Shared\Services\LocationService;
use Src\Domain\Session\Models\Session;
use Src\Domain\User\Models\User;
use Throwable;

class AuthController
{
    /**
     * Authenticate user and generate token
     *
     * @throws Throwable
     */
    public function login(LocationService $locationService, LoginData $loginData): Response|JsonResponse
    {
        return DB::transaction(function () use ($loginData, $locationService): Response {
            $user = User::query()->where('email', $loginData->email)->first();

            $token = $user->createToken('auth-token')->plainTextToken;

            $ipAddress = request()->ip();

            $location = $locationService->getLocationFromIp($ipAddress);

            $user->update([
                'last_login_ip' => $ipAddress,
                'last_login_at' => now(),
            ]);

            // Register session with location (create a new session record for each login)
            Session::query()->create([
                'id' => Str::random(40),
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
                'location' => $location,
                'payload' => serialize(['login_at' => now()->toDateTimeString()]),
                'last_activity' => now()->timestamp,
            ]);

            return response(AuthResource::fromModel($user, $token)->toArray(), 200);
        });
    }
}
