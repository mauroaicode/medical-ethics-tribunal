<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Src\Application\Admin\StepUp\Data\SendCodeData;
use Src\Application\Admin\StepUp\Data\VerifyCodeData;
use Src\Application\Admin\StepUp\Services\SendStepUpCodeService;
use Src\Application\Admin\StepUp\Services\StepUpMessageService;
use Src\Application\Admin\StepUp\Services\VerifyStepUpCodeService;
use Throwable;

class StepUpController
{
    /**
     * Send OTP code to authenticated user's email
     *
     * @throws Throwable
     */
    public function sendCode(
        SendStepUpCodeService $sendCodeService,
        SendCodeData $sendCodeData
    ): Response {

        $user = Auth::user();

        if (! $user) {
            abort(401, __('auth.unauthorized'));
        }

        $sendCodeService->handle($user, $sendCodeData->action);

        return response(['message' => __('step_up.code_sent')], 200);
    }

    /**
     * Verify OTP code
     *
     * @throws Throwable
     */
    public function verifyCode(
        VerifyStepUpCodeService $verifyCodeService,
        StepUpMessageService $messageService,
        VerifyCodeData $verifyCodeData
    ): Response|JsonResponse {
        $user = Auth::user();

        if (! $user) {
            abort(401, __('auth.unauthorized'));
        }

        $result = $verifyCodeService->handle($user, $verifyCodeData->action, $verifyCodeData->code);

        if (! $result['valid']) {
            $errorResponse = $messageService->handle($result['remaining_attempts']);

            return response($errorResponse, 422);
        }

        return response([
            'message' => __('step_up.code_verified'),
            'action' => $verifyCodeData->action,
            'verified' => true,
        ], 200);
    }
}
