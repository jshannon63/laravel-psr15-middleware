<?php

declare(strict_types=1);

namespace Jshannon63\Psr15Middleware;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class Handler implements RequestHandlerInterface
{
    protected $response;
    protected $request;

    public function __construct(FoundationResponse $response)
    {
        $psr17Factory = new Psr17Factory();
        $this->response = (new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        ))->createResponse($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
