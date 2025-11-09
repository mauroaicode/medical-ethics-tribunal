<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Src\Application\Admin\StepUp\Controllers\StepUpController;

Route::middleware(['auth:sanctum'])->prefix('step-up')->group(function (): void {
    Route::post('send-code', [StepUpController::class, 'sendCode']);
    Route::post('verify-code', [StepUpController::class, 'verifyCode']);
});
