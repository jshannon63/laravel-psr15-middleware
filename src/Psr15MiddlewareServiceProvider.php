<?php

namespace Jshannon63\Psr15Middleware;

use Illuminate\Support\ServiceProvider;

class Psr15MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $middlewaresource = realpath(__DIR__.'/../publish/Psr15Middleware.php');
        $middlewaredestination = app_path('Http/Middleware/Psr15Middleware.php');

        $groupmiddlewaresource = realpath(__DIR__.'/../publish/Psr15GroupMiddleware.php');
        $groupmiddlewaredestination = app_path('Http/Middleware/Psr15GroupMiddleware.php');

        $routemiddlewaresource = realpath(__DIR__.'/../publish/Psr15RouteMiddleware.php');
        $routemiddlewaredestination = app_path('Http/Middleware/Psr15RouteMiddleware.php');

        $this->publishes([
            $middlewaresource => $middlewaredestination,
            $groupmiddlewaresource => $groupmiddlewaredestination,
            $routemiddlewaresource => $routemiddlewaredestination,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
