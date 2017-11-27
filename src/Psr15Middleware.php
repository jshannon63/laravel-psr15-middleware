<?php

namespace Jshannon63\Psr15Middleware;

use Closure;

class Psr15Middleware
{
    protected $tuff;

    public function handle($request, Closure $next)
    {
        // execute the foundation middleware stack to get the
        // response before running the psr15 middleware stack.
        $response = $next($request);

        $dispatcher = new Dispatcher();

        return $dispatcher($request, $response, $this->middleware);

    }

}