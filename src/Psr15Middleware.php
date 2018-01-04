<?php

namespace Jshannon63\Psr15Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository;

class Psr15Middleware
{
    protected $middleware;

    protected $config;

    public function __construct(Repository $config, string $middleware = 'middleware')
    {
        $this->config = $config;
        $this->middleware = $this->config->get('psr15middleware.'.$middleware);
        if (gettype($this->middleware) != 'array') {
            $this->middleware = [$this->middleware];
        }
    }

    public function handle($request, Closure $next, ...$parameters)
    {
        // execute the foundation middleware stack to get the
        // response before running the psr15 middleware stack.
        $response = $next($request);

        $dispatcher = new Dispatcher;

        return $dispatcher($request, $response, $this->middleware, ...$parameters);
    }
}
