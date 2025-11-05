<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\GoogleAuth\Controllers\GoogleAuthController;

Route::prefix('google-auth')->group(function (): void {
    Route::get('/callback', [GoogleAuthController::class, 'handleCallback']);
});

// Authenticated routes
Route::middleware(['auth:sanctum'])->prefix('google-auth')->group(function (): void {
    Route::get('/auth-url', [GoogleAuthController::class, 'getAuthUrl']);
    Route::post('/callback', [GoogleAuthController::class, 'authenticate']);
});
