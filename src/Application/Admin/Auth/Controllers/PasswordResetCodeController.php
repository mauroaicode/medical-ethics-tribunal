<?php

declare(strict_types=1);

namespace Src\Application\Admin\Auth\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Random\RandomException;
use Src\Application\Admin\Auth\Data\ForgotPasswordData;
use Src\Application\Admin\Auth\Data\ResetPasswordData;
use Src\Application\Admin\Auth\Data\VerifyResetCodeData;
use Src\Application\Admin\Auth\Notifications\ForgotPasswordNotification;
use Src\Domain\User\Models\User;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetCodeController
{
    /**
     * @throws RandomException
     */
    public function store(ForgotPasswordData $forgotPasswordData): Response|JsonResponse
    {
        $user = User::query()->where('email', $forgotPasswordData->email)->first();

        if (! $user) {
            return response()->json([
                'messages' => [__('auth.failed')],
                'code' => 422,
            ], 422);
        }

        $verificationCode = random_int(100000, 999999);

        Cache::put(
            key: 'password_reset_'.$user->email,
            value: $verificationCode,
            ttl: now()->addMinutes(config()->integer('auth.expiration_time_code_forgot_password')),
        );

        $user->notify(new ForgotPasswordNotification((string) $verificationCode));

        return new Response(status: 200);
    }

    public function update(ResetPasswordData $resetPasswordData): Response|JsonResponse
    {
        $cachedVerificationCode = Cache::get('password_reset_'.$resetPasswordData->email);

        if ($cachedVerificationCode !== $resetPasswordData->code) {
            return response()->json([
                'messages' => [__('validation.invalid_or_expired_code')],
                'code' => 422,
            ], 422);
        }

        $user = User::query()->where('email', $resetPasswordData->email)->first();

        if (! $user) {
            return response()->json([
                'messages' => [__('auth.failed')],
                'code' => 422,
            ], 422);
        }

        $user->update(['password' => Hash::make($resetPasswordData->password)]);
        Cache::forget('password_reset_'.$resetPasswordData->email);
        event(new PasswordReset($user));

        return new Response(status: 204);
    }

    public function verifyPasswordResetCode(VerifyResetCodeData $verifyResetCodeData): Response|JsonResponse
    {
        $cachedCode = (int) Cache::get('password_reset_'.$verifyResetCodeData->email);

        if ($cachedCode !== $verifyResetCodeData->code) {
            return response()->json([
                'messages' => [__('validation.invalid_or_expired_code')],
                'code' => 422,
            ], 422);
        }

        return new Response(status: 200);
    }
}
