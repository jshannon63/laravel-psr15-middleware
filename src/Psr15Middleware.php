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
            $this->middleware = array($this->middleware);
        }

    }

    public function handle($request, Closure $next)
    {
        $args = array_slice(func_get_args(), 2);
        if (count($this->middleware) === 1 && count($args) > 0) {
            if (gettype($this->middleware[0]) === 'string') {
                $rCls = new \ReflectionClass($this->middleware[0]);
            } elseif (gettype($this->middleware[0]) === 'object') {
                $rCls = new \ReflectionClass(get_class($this->middleware[0]));
            }
            $this->middleware[0] = $rCls->newInstanceArgs($args);
        }
        // execute the foundation middleware stack to get the
        // response before running the psr15 middleware stack.
        $response = $next($request);

        $dispatcher = new Dispatcher;

        return $dispatcher($request, $response, $this->middleware);

    }

}
