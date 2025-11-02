<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\Auth\Controllers\AuthController;
use Src\Application\Admin\Auth\Controllers\EmailVerificationController;
use Src\Application\Admin\Auth\Controllers\PasswordResetCodeController;

Route::prefix('auth')->group(function (): void {
    Route::post('login', [AuthController::class, 'login']);
    Route::get('verify/{id}/{hash}', EmailVerificationController::class)->name('verification.verify');
    Route::post('forgot-password', [PasswordResetCodeController::class, 'store'])->name('password.email');
    Route::put('reset-password', [PasswordResetCodeController::class, 'update'])->name('password.reset');
    Route::post('verify-password-reset-code', [PasswordResetCodeController::class, 'verifyPasswordResetCode']);
});
