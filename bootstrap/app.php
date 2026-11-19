
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/accounts.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Language middleware
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        // Custom admin middleware
        $middleware->alias([
            'admin.active' => \App\Http\Middleware\EnsureAdminIsActive::class,
        ]);

        // Login redirects
        $middleware->redirectGuestsTo(function ($request) {

            if ($request->is('super-admin/*')) {
                return route('super_admin.login');
            }

            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
