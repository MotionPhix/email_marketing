<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    channels: __DIR__ . '/../routes/channels.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
      \App\Http\Middleware\HandleInertiaRequests::class,
      \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->alias([
      'onboarding.complete' => \App\Http\Middleware\EnsureOnboardingIsComplete::class,
      'settings.completed' => \App\Http\Middleware\CheckSettings::class,
    ]);

    $middleware->api([
      \Illuminate\Session\Middleware\StartSession::class,
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      \Illuminate\Cookie\Middleware\EncryptCookies::class,
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
      \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);

    $middleware->validateCsrfTokens(except: [
      '/analytics',
      '/webhooks/paychangu',
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    //
  })->create();
