<?php

namespace Jshannon63\Psr15Middleware;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Dispatcher
{
    public function __invoke(FoundationRequest $request, FoundationResponse $response, $middleware)
    {
        $psr7request = (new DiactorosFactory())->createRequest($request);

        $requestHandler = new Handler($response);

        if (is_callable($item)) {
            $psr7response = $item()->process($requestHandler->getRequest(), $requestHandler);
        } elseif (is_object($item)) {
            $psr7response = $item->process($requestHandler->getRequest(), $requestHandler);
        } else {
            $psr7response = (new $item)->process($requestHandler->getRequest(), $requestHandler);
        }

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
