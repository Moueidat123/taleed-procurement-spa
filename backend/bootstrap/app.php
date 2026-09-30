<?php

use App\Http\Middleware\AssignRequestId;
use App\Support\ApiErrorRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/procurement/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // First-party SPA on the same origin: session + CSRF for /api (Sanctum).
        $middleware->statefulApi();
        $middleware->prepend(AssignRequestId::class);

        // API guests get a JSON 401 (never a redirect); the SPA owns its login screen.
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : '/#/login');

        // Only the local/production reverse proxy may set X-Forwarded-*.
        // TRUSTED_PROXIES is a comma-separated CIDR list of the proxy network.
        $proxies = array_filter(explode(',', (string) env('TRUSTED_PROXIES', '')));
        if ($proxies !== []) {
            $middleware->trustProxies(at: $proxies);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(fn (Throwable $e, Request $request) => $request->is('api/*')
            ? ApiErrorRenderer::render($e, $request)
            : null);
    })->create();
