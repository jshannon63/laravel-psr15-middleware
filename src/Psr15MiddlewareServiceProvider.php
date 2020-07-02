<?php

declare(strict_types=1);

namespace Jshannon63\Psr15Middleware;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class Psr15MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publish();

        $config = $this->app['config'];

        if ($config->get('psr15middleware')) {
            foreach ($config->get('psr15middleware.middleware') ?? [] as $key => $middleware) {
                $this->app->singleton('psr15.middleware.'.$key, static function () use ($middleware) {
                    return new Psr15Middleware($middleware[0], $middleware[2]);
                });
                if ($middleware[1] === 'prepend') {
                    $this->app[Kernel::class]->prependMiddleware('psr15.middleware.'.$key);
                } else {
                    $this->app[Kernel::class]->pushMiddleware('psr15.middleware.'.$key);
                }
            }
            foreach ($config->get('psr15middleware.groups') ?? [] as $groupKey => $group) {
                foreach ($config->get('psr15middleware.groups.'.$groupKey) as $key => $middleware) {
                    $this->app->bind('psr15.group.'.strtolower($groupKey).'.'.$key, static function () use ($middleware): Psr15Middleware {
                        return new Psr15Middleware($middleware[0], $middleware[2]);
                    });
                    if ($middleware[1] === 'prepend') {
                        $this->app['router']->prependMiddlewareToGroup($groupKey, 'psr15.group.'.strtolower($groupKey).'.'.$key);
                    } else {
                        $this->app['router']->pushMiddlewareToGroup($groupKey, 'psr15.group.'.strtolower($groupKey).'.'.$key);
                    }
                }
            }
            foreach ($config->get('psr15middleware.aliases') ?? [] as $key => $middleware) {
                $this->app->bind('psr15.alias.'.strtolower($key), static function () use ($middleware): Psr15Middleware {
                    return new Psr15Middleware($middleware[0], $middleware[2]);
                });
                $this->app['router']->aliasMiddleware($key, 'psr15.alias.'.strtolower($key));
            }
        }
    }

    public function register(): void
    {
    }

    private function publish(): void
    {
        $this->publishes([
            __DIR__ . '/../config/psr15middleware.php' => config_path('psr15middleware.php'),
        ]);
    }
}
