<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // DO App Platform terminates TLS at its load balancer and forwards
        // to the app over HTTP with X-Forwarded-* headers. Trusting any
        // upstream makes url()/route() generate HTTPS correctly and lets
        // secure cookies work end-to-end.
        $middleware->trustProxies(at: '*');

        // Resolve trusted hosts from APP_URL. Falls back to 'localhost' if
        // APP_URL isn't parseable — never an empty array, which Laravel
        // treats as "trust all hosts."
        $middleware->trustHosts(at: function () {
            $host = parse_url((string) config('app.url'), PHP_URL_HOST);

            return $host ? [$host] : ['localhost'];
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
