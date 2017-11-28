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

        $this->publishes([
            __DIR__.'/../config/psr15middleware.php' => config_path('psr15middleware.php'),
        ]);

        $config = $this->app['config'];

        $this->app->singleton('Psr15Middleware', function () use ($config) {
            return new \Jshannon63\Psr15Middleware\Psr15Middleware($config, 'middleware');
        });
        $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware('Psr15Middleware');

        foreach ($config->get('psr15middleware.groups') as $key => $group) {
            $this->app->singleton('Psr15MiddlewareGroup'.title_case($key), function () use ($config, $key) {
                return new \Jshannon63\Psr15Middleware\Psr15Middleware($config, 'groups.'.$key);
            });
            $this->app['router']->pushMiddlewareToGroup($key, 'Psr15MiddlewareGroup'.title_case($key));
        }

        foreach ($config->get('psr15middleware.aliases') as $key => $alias) {
            $this->app->singleton('Psr15MiddlewareAlias'.title_case($key), function () use ($config, $key) {
                return new \Jshannon63\Psr15Middleware\Psr15Middleware($config, 'aliases.'.$key);
            });

            $this->app['router']->aliasMiddleware($key, 'Psr15MiddlewareAlias'.title_case($key));
        }
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
