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

        if ($config->get('psr15middleware')) {
            foreach ($config->get('psr15middleware.middleware') as $key => $middleware) {
                $this->app->singleton('Psr15MiddlewareMiddleware'.$key, function () use ($middleware) {
                    return new \Jshannon63\Psr15Middleware\Psr15Middleware($middleware[0], $middleware[1]);
                });
                dd($this->app['Psr15MiddlewareMiddleware'.$key]);

                $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware('Psr15MiddlewareMiddleware'.$key);
            }
            foreach ($config->get('psr15middleware.groups') as $groupkey => $group) {
                foreach ($config->get('psr15middleware.groups.'.$groupkey) as $key => $middleware) {
                    $this->app->bind('Psr15MiddlewareGroup'.title_case($groupkey).$key, function () use ($middleware) {
                        return new Psr15Middleware($middleware[0], $middleware[1]);
                    });
                    $this->app['router']->pushMiddlewareToGroup($groupkey, 'Psr15MiddlewareGroup'.title_case($groupkey).$key);
                }
            }
            foreach ($config->get('psr15middleware.aliases') as $key => $middleware) {
                $this->app->bind('Psr15MiddlewareAlias'.title_case($key), function () use ($middleware) {
                    return new Psr15Middleware($middleware[0], $middleware[1]);
                });
                $this->app['router']->aliasMiddleware($key, 'Psr15MiddlewareAlias'.title_case($key));
            }
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
