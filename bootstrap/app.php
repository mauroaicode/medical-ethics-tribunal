<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json(
                    data: [
                        'messages' => [$e->getMessage()],
                        'code' => $e->getStatusCode(),
                    ],
                    status: $e->getStatusCode(),
                )->setStatusCode($e->getStatusCode());
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $messages = $e->validator->getMessageBag()->getMessages();
                $mappedMessages = [];
                foreach ($messages as $fieldMessages) {
                    foreach ($fieldMessages as $errorMessage) {
                        $mappedMessages[] = $errorMessage;
                    }
                }

                return response()->json(
                    data: [
                        'messages' => $mappedMessages,
                        'code' => 422,
                    ],
                    status: 422,
                );
            }
        });

        $exceptions->render(function (RouteNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {

                if (str_contains($e->getMessage(), 'login')) {
                    return response()->json(
                        [
                            'messages' => [__('auth.unauthorized')],
                            'code' => 401,
                        ],
                        status: 401,
                    );
                }

                return response()->json(
                    [
                        'messages' => ['Endpoint not found.'],
                        'code' => 404,
                    ],
                    status: 404,
                );
            }
        });
    })->create();
