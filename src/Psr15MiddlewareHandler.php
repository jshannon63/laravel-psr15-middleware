<?php

namespace Jshannon63\Psr15Middleware;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


Class Handler implements RequestHandlerInterface
{
    protected $response;

    public function __construct(FoundationResponse $response)
    {
        $this->response = (new DiactorosFactory())->createResponse($response);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }

    public function set($response)
    {
        $this->response = $response;

    }
}