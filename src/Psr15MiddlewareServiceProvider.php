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
                $this->app->singleton('psr15.middleware.'.$key, function () use ($middleware) {
                    return new Psr15Middleware($middleware[0], $middleware[2]);
                });
                if ($middleware[1] == 'prepend') {
                    $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependMiddleware('psr15.middleware.'.$key);
                } else {
                    $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware('psr15.middleware.'.$key);
                }
            }
            foreach ($config->get('psr15middleware.groups') as $groupkey => $group) {
                foreach ($config->get('psr15middleware.groups.'.$groupkey) as $key => $middleware) {
                    $this->app->bind('psr15.group.'.strtolower($groupkey).'.'.$key, function () use ($middleware) {
                        return new Psr15Middleware($middleware[0], $middleware[2]);
                    });
                    if ($middleware[1] == 'prepend') {
                        $this->app['router']->prependMiddlewareToGroup($groupkey, 'psr15.group.'.strtolower($groupkey).'.'.$key);
                    } else {
                        $this->app['router']->pushMiddlewareToGroup($groupkey, 'psr15.group.'.strtolower($groupkey).'.'.$key);
                    }
                }
            }
            foreach ($config->get('psr15middleware.aliases') as $key => $middleware) {
                $this->app->bind('psr15.alias.'.strtolower($key), function () use ($middleware) {
                    return new Psr15Middleware($middleware[0], $middleware[2]);
                });
                $this->app['router']->aliasMiddleware($key, 'psr15.alias.'.strtolower($key));
            }
            // dd($this);
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
