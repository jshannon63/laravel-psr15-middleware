<?php

namespace Jshannon63\Psr15Middleware;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Dispatcher
{
    public function __invoke(FoundationRequest $request, FoundationResponse $response, array $middleware)
    {
        $psr7request = (new DiactorosFactory())->createRequest($request);

        $requestHandler = new Handler($response);

        $psr7response = array_reduce(
            $middleware,
            function ($carry, $item) use ($psr7request, $requestHandler) {
                $requestHandler->set($carry);
                return (new $item)->process($psr7request, $requestHandler);
            },
            $requestHandler->handle($psr7request)
        );

        return (new HttpFoundationFactory())->createResponse($psr7response);
    }
}