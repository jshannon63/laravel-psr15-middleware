<?php

namespace Jshannon63\Psr15Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class exampleMiddleware implements MiddlewareInterface
{
    public function __construct()
    {
        // if needed
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // process request/response obects here
        $response->getBody()->write("<h1>PSR-15 Middleware Rocks!</h1>");


        return $response;
    }
}

