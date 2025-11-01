<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
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
    })->create();
