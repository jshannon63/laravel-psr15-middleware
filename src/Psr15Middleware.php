<?php

declare(strict_types=1);

namespace Jshannon63\Psr15Middleware;

use Closure;

class Psr15Middleware
{
    protected $middleware;
    protected $mode;

    public function __construct($middleware, $mode = 'before')
    {
        $this->middleware = $middleware;
        $this->mode = $mode;
    }

    /**
     * Laravel compatible middleware handle method
     *
     * @param [type] $request
     * @param Closure $next
     * @param [type] ...$parameters
     * @return void
     */
    public function handle($request, Closure $next, ...$parameters)
    {
        $dispatcher = new Dispatcher();

        if ($this->mode === 'before') {
            // we must create a mock response object since PSR-15 requires it
            // but it is not truly available at this point in the request cycle.
            // so we will ignore it when returned.
            $messages = $dispatcher($request, (new \Symfony\Component\HttpFoundation\Response), $this->middleware, ...$parameters);
            return $next($messages['request']);
        }

        if ($this->mode === 'after') {
            $response = $next($request);
            $messages = $dispatcher($request, $response, $this->middleware, ...$parameters);
            return $messages['response'];
        }

        return $next($request);
    }

    /**
     * for terminable middlewares
     *
     * @param [type] $request
     * @param [type] $response
     * @param [type] ...$parameters
     * @return void
     */
    public function terminate($request, $response, ...$parameters): void
    {
        if ($this->mode === 'terminable') {
            (new Dispatcher())($request, $response, $this->middleware, ...$parameters);
        }
    }
}
