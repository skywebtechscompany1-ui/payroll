<?php

use App\Console\Kernel as ConsoleKernel;
use App\Exceptions\Handler as AppExceptionHandler;
use App\Http\Kernel as HttpKernel;
use App\Http\Middleware\ForceHttps;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ForceHttps::class);

        $middleware->alias([
            'role' => \Laratrust\Middleware\LaratrustRole::class,
            'permission' => \Laratrust\Middleware\LaratrustPermission::class,
            'ability' => \Laratrust\Middleware\LaratrustAbility::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withBindings([
        HttpKernelContract::class => HttpKernel::class,
        ConsoleKernelContract::class => ConsoleKernel::class,
        ExceptionHandlerContract::class => AppExceptionHandler::class,
    ])->create();
