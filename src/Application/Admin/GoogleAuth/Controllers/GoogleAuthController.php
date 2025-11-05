<?php

declare(strict_types=1);

namespace Src\Application\Admin\GoogleAuth\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Src\Application\Shared\Services\GoogleDriveService;

readonly class GoogleAuthController
{
    public function __construct(
        private GoogleDriveService $googleDriveService
    ) {}

    /**
     * Get Google Drive authorization URL
     */
    public function getAuthUrl(): Response
    {
        try {
            $authUrl = $this->googleDriveService->getAuthorizationUrl();

            Log::channel('google')->info('Authorization URL requested', [
                'ip' => request()->ip(),
            ]);

            return response([
                'auth_url' => $authUrl,
            ], 200);

        } catch (Exception $e) {

            Log::channel('google')->error('Failed to generate authorization URL', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);

            return response([
                'message' => __('google_auth.failed_to_generate_url'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Google OAuth callback (GET request when Google redirects)
     */
    public function handleCallback(Request $request): Response
    {
        try {
            $code = $request->query('code');

            if (! $code) {
                Log::channel('google')->warning('Callback received without authorization code', [
                    'ip' => request()->ip(),
                    'query_params' => $request->query(),
                ]);

                return response()->view('google-auth-callback', [
                    'success' => false,
                    'message' => __('google_auth.authentication_failed'),
                    'error' => 'Authorization code not provided',
                ], 400);
            }

            $this->googleDriveService->authenticate($code);

            Log::channel('google')->info('Google authentication successful', [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Return success view
            return response()->view('google-auth-callback', [
                'success' => true,
                'message' => __('google_auth.authenticated_successfully'),
            ], 200);

        } catch (Exception $e) {

            Log::channel('google')->error('Google authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
            ]);

            return response()->view('google-auth-callback', [
                'success' => false,
                'message' => __('google_auth.authentication_failed'),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Authenticate with Google Drive using authorization code (POST request for manual submission)
     */
    public function authenticate(Request $request): Response
    {
        try {
            $request->validate([
                'code' => ['required', 'string'],
            ]);

            $this->googleDriveService->authenticate($request->input('code'));

            return response([
                'message' => __('google_auth.authenticated_successfully'),
            ], 200);

        } catch (ValidationException $e) {

            Log::channel('google')->warning('Google authentication validation failed', [
                'errors' => $e->errors(),
                'ip' => request()->ip(),
            ]);

            return response([
                'message' => __('google_auth.validation_failed'),
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $e) {
            Log::channel('google')->error('Google authentication failed (manual)', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => request()->ip(),
            ]);

            return response([
                'message' => __('google_auth.authentication_failed'),
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
