<?php

use App\Exceptions\BusinessRuleException;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

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
        /*
         * Every API failure answers with the same envelope the successful
         * responses use, so the mobile client never has to parse an HTML error
         * page or guess at the shape of a payload.
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            // Scoped to the API prefix on purpose: Livewire/Filament requests
            // also expect JSON and must keep their own error format.
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof BusinessRuleException => ApiResponse::error(
                    $e->getMessage(),
                    $e->getStatusCode(),
                    [],
                    $e->getContext()
                ),
                $e instanceof ValidationException => ApiResponse::error(
                    $e->validator->errors()->first(),
                    422,
                    $e->errors()
                ),
                $e instanceof AuthenticationException => ApiResponse::error(
                    'Sesi Anda telah berakhir. Silakan masuk kembali.',
                    401
                ),
                $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException => ApiResponse::error(
                    'Data yang diminta tidak ditemukan.',
                    404
                ),
                $e instanceof TooManyRequestsHttpException => ApiResponse::error(
                    'Terlalu banyak permintaan. Silakan coba lagi sebentar lagi.',
                    429
                ),
                $e instanceof HttpExceptionInterface => ApiResponse::error(
                    $e->getMessage() ?: 'Permintaan tidak dapat diproses.',
                    $e->getStatusCode()
                ),
                default => config('app.debug')
                    ? null
                    : ApiResponse::error('Terjadi kesalahan pada server. Silakan coba lagi.', 500),
            };
        });
    })->create();
