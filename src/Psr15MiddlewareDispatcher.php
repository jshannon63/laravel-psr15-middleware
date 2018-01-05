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
        $clone = clone $original;

        $foundation_request = (new HttpFoundationFactory())->createRequest($psr7request);

        $clone->attributes = $foundation_request->attributes;
        $clone->request = $foundation_request->request;
        $clone->server = $foundation_request->server;
        $clone->query = $foundation_request->query;
        $clone->files = $foundation_request->files;
        $clone->cookies = $foundation_request->cookies;
        $clone->headers = $foundation_request->headers;

        $clone->setRequestFormat($foundation_request->getRequestFormat());
        $clone->setDefaultLocale($foundation_request->getDefaultLocale());
        $clone->setLocale($foundation_request->getLocale());
        if ($foundation_request->hasSession()) {
            $clone->setSession($foundation_request->getSession());
        }

        return $clone;
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
