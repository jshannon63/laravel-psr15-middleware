<?php

namespace Tests;

use Jshannon63\Psr15Middleware\Psr15Middleware;

require_once __DIR__."/../src/Psr15MiddlewareDispatcher.php";
require_once __DIR__."/../src/Psr15MiddlewareHandler.php";


use Illuminate\Config\Repository;
use Illuminate\Container\Container;
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


class Psr15MiddlewareTest extends TestCase
{
    protected $middleware;
    protected $container;
    protected $config;

    public function setUp()
    {
        parent::setUp();
        $this->middleware = [
            'psr15middleware.middleware' => [
                \Tests\exampleMiddleware1::class,
                function(){
                    return new \Tests\exampleMiddleware2();
                },
                (new \Tests\exampleMiddleware3())
            ]
        ];
        $this->container = new Container;
        $this->config = new Repository($this->middleware);
    }

    public function test_middleware_stack(){

        $request = Request::create('http://localhost:8888/test/1', 'GET', [], [], [], [], null);
        $response = new Response('Original Content:', Response::HTTP_OK, array('content-type' => 'text/html'));

        $psr15middleware = new Psr15Middleware($this->config,'middleware');

        $result = $psr15middleware->handle($request, function() use ($response){
            return $response;
        });

        $this->assertContains('Test-1',$result->getContent());
        $this->assertContains('Test-2',$result->getContent());
        $this->assertContains('Test-3',$result->getContent());
        $this->assertContains('<h1>Test-1</h1><h1>Test-2</h1><h1>Test-3</h1>',$result->getContent());

    }
}