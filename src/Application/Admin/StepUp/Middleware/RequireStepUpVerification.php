<?php

declare(strict_types=1);

namespace Src\Application\Admin\StepUp\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Src\Application\Admin\StepUp\Services\CheckStepUpBlockService;
use Src\Application\Admin\StepUp\Services\SendStepUpCodeService;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

readonly class RequireStepUpVerification
{
    public function __construct(
        private CheckStepUpBlockService $checkBlockService,
        private SendStepUpCodeService $sendCodeService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, __('auth.unauthorized'));
        }

        $this->checkBlockService->handle($user, $action);

        $prefix = config('step-up.verification.cache_key_prefix');
        $verificationKey = "{$prefix}_{$user->id}_{$action}";
        $isVerified = Cache::get($verificationKey, false);

        if (! $isVerified) {
            $this->sendCodeService->handle($user, $action);

            abort(428, __('step_up.code_sent_please_verify', [
                'action' => Lang::get('step_up.actions')[$action] ?? $action,
            ]));
        }

        $response = $next($request);

        if (in_array($response->getStatusCode(), [200, 201, 204], true)) {
            Cache::forget($verificationKey);
        }

        return $response;
    }
}
