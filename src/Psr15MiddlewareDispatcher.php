<?php

namespace Jshannon63\Psr15Middleware;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;

class Dispatcher
{
    public function __invoke(FoundationRequest $request, FoundationResponse $response, $middleware, ...$parameters)
    {
        $psr7request = (new DiactorosFactory())->createRequest($request);

        $requestHandler = new Handler($response);

        if (is_callable($middleware)) {
            $psr7response = $middleware()->process($psr7request, $requestHandler);
        } elseif (is_object($middleware)) {
            $psr7response = $middleware->process($psr7request, $requestHandler);
        } else {
            $psr7response = (new $middleware(...$parameters))->process($psr7request, $requestHandler);
        }

        return [
            'request' => $this->convertRequest($requestHandler->getRequest(), $request),
            'response' => $this->convertResponse($psr7response, $response)
        ];
    }

    private function convertRequest($psr7request, $original)
    {
        $foundation_request = (new HttpFoundationFactory())->createRequest($psr7request);

        $original->query = clone $foundation_request->query;
        $original->request = clone $foundation_request->request;
        $original->attributes = clone $foundation_request->attributes;
        $original->cookies = clone $foundation_request->cookies;
        $original->files = clone $foundation_request->files;
        $original->server = clone $foundation_request->server;
        $original->headers = clone $foundation_request->headers;

        return $original;
    }

    private function convertResponse($psr7response, $original)
    {
        $clone = clone $original;

        $foundation_response = (new HttpFoundationFactory())->createResponse($psr7response);

        foreach ($foundation_response->headers as $key => $value) {
            $clone->headers->set($key, $value);
        }
        $clone->setContent($foundation_response->getContent());
        $clone->setProtocolVersion($foundation_response->getProtocolVersion());
        $clone->setStatusCode($foundation_response->getStatusCode());
        $clone->setCharset($foundation_response->getCharset());

        return $clone;
    }
}
