<?php

namespace Jshannon63\Psr15Middleware;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Dispatcher
{
    public function __invoke(FoundationRequest $request, FoundationResponse $response, array $middleware, ...$parameters)
    {
        $psr7request = (new DiactorosFactory())->createRequest($request);

        $requestHandler = new Handler($response);

        $psr7response = array_reduce(
            $middleware,
            function ($carry, $item) use ($requestHandler, $parameters) {
                $requestHandler->setResponse($carry);

                if (is_callable($item)) {
                    return $item()->process($requestHandler->getRequest(), $requestHandler);
                }

                if (is_object($item)) {
                    return $item->process($requestHandler->getRequest(), $requestHandler);
                }

                return (new $item(...$parameters))->process($requestHandler->getRequest(), $requestHandler);
            },
            $requestHandler->handle($psr7request)
        );

        return $this->convertResponse($psr7response, $response);
    }

    private function convertResponse($psr7response, $original)
    {
        $foundation_response = (new HttpFoundationFactory())->createResponse($psr7response);

        foreach ($foundation_response->headers as $key => $value) {
            $original->headers->set($key, $value);
        }
        $original->setContent($foundation_response->getContent());
        $original->setProtocolVersion($foundation_response->getProtocolVersion());
        $original->setStatusCode($foundation_response->getStatusCode());
        $original->setCharset($foundation_response->getCharset());

        return $original;
    }
}
