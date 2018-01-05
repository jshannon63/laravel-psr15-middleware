<?php

namespace Jshannon63\Psr15Middleware;

use Closure;

class Psr15Middleware
{
    protected $middleware;
    protected $mode;

    public function __construct($middleware, $mode = 'before')
    {
        $this->middleware = $middleware;
    }

    public function handle($request, Closure $next)
    {
        $dispatcher = new Dispatcher;

        if ($this->mode == 'before') {
            // we create a throw-away response object since PSR-15 requires it but it is not
            // truly available at this point in the request cycle.
            $dispatcher($request, (new \Symfony\Component\HttpFoundation\Response), $this->middleware);
            return $next($request);
        } elseif ($this->mode == 'after') {
            $response = $next($request);
            return $dispatcher($request, $response, $this->middleware);
        } else {
            return $next($request);
        }
    }

    public function terminate($request, $response)
    {
        if ($this->mode == 'terminable') {
            $dispatcher = new Dispatcher;
            $dispatcher($request, $response, $this->middleware);
        }
    }
}
