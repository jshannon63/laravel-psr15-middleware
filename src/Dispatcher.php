<?php

declare(strict_types=1);

namespace Jshannon63\Psr15Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Component\HttpFoundation\Request as FoundationRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

class Dispatcher
{
    public function __invoke(FoundationRequest $request, FoundationResponse $response, $middleware, ...$parameters)
    {
        $psr17Factory = new Psr17Factory();
        $psr7request = (new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        ))->createRequest($request);

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
        $foundationRequest = (new HttpFoundationFactory())->createRequest($psr7request);

        $original->query = clone $foundationRequest->query;
        $original->request = clone $foundationRequest->request;
        $original->attributes = clone $foundationRequest->attributes;
        $original->cookies = clone $foundationRequest->cookies;
        $original->files = clone $foundationRequest->files;
        $original->server = clone $foundationRequest->server;
        $original->headers = clone $foundationRequest->headers;

        return $original;
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
        $original->setCharset($foundation_response->getCharset() ?: '');

        return $original;
    }
}
