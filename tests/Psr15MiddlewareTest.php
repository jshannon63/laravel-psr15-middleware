<?php

namespace Tests;

require_once __DIR__."/../src/Psr15MiddlewreDispatcher.php";
require_once __DIR__."/../src/Psr15MiddlewareHandler.php";

use Jshannon63\Psr15Middleware\Psr15Middleware as Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Framework\TestCase;
use Illuminate\Http\Response;
use Illuminate\Http\Request;


class exampleMiddleware1 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write("<h1>Test-1</h1>");

        return $response;
    }
}

class exampleMiddleware2 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write("<h1>Test-2</h1>");

        return $response;
    }
}

class exampleMiddleware3 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->write("<h1>Test-3</h1>");

        return $response;
    }
}

class Psr15Middleware extends Middleware
{
    protected $middleware = [

        \Tests\exampleMiddleware1::class,
        \Tests\exampleMiddleware2::class,
        \Tests\exampleMiddleware3::class,

    ];
}

class Psr15MiddlewareTest extends TestCase
{

    public function test_middleware_stack(){

        $request = Request::create('http://localhost:8888/test/1', 'GET', [], [], [], [], null);
        $response = new Response('Original Content:', Response::HTTP_OK, array('content-type' => 'text/html'));

        $psr15middleware = new Psr15Middleware();

        $result = $psr15middleware->handle($request, function() use ($response){
            return $response;
        });

        $this->assertContains('Test-1',$result->getContent());
        $this->assertContains('Test-2',$result->getContent());
        $this->assertContains('Test-3',$result->getContent());
        $this->assertContains('<h1>Test-1</h1><h1>Test-2</h1><h1>Test-3</h1>',$result->getContent());

    }
}