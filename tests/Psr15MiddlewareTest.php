<?php

namespace Tests;

use Jshannon63\Psr15Middleware\Psr15Middleware;

require_once __DIR__.'/../src/Psr15MiddlewareDispatcher.php';
require_once __DIR__.'/../src/Psr15MiddlewareHandler.php';

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
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

        $response->getBody()->rewind();
        $body = $response->getBody();
        $contents = $body->getContents();
        $contents .= '<h1>Test-1</h1>';
        $body->rewind();
        $body->write($contents);

        return $response->withBody($body);
    }
}

class exampleMiddleware2 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->rewind();
        $body = $response->getBody();
        $contents = $body->getContents();
        $contents .= '<h1>Test-2</h1>';
        $body->rewind();
        $body->write($contents);

        return $response->withBody($body);
    }
}

class exampleMiddleware3 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $response->getBody()->rewind();
        $body = $response->getBody();
        $contents = $body->getContents();
        $contents .= '<h1>Test-3</h1>';
        $body->rewind();
        $body->write($contents);

        return $response->withBody($body);
    }
}

class exampleMiddleware4 implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request->withHeader('X-PHPUNIT-TEST', 'PASSED');

        $response = $handler->handle($request);

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
                [\Tests\exampleMiddleware1::class, 'append', 'after'],
                [function () {
                    return new \Tests\exampleMiddleware2();
                }, 'append', 'after'],
                [(new \Tests\exampleMiddleware3()), 'append', 'after'],
                [(new \Tests\exampleMiddleware4()), 'append', 'after']
            ]
        ];
        $this->container = new Container;
        $this->config = new Repository($this->middleware);
    }

    public function test_middleware_stack()
    {
        $request = Request::create('http://localhost:8888/test/1', 'GET', [], [], [], [], null);
        $response = new Response('Original Content:', Response::HTTP_OK, ['content-type' => 'text/html']);

        $middlewares = $this->config->get('psr15middleware.middleware');

        foreach ($middlewares as $middleware) {
            $psr15middleware = new Psr15Middleware($middleware[0], $middleware[2]);
            $response = $psr15middleware->handle($request, function () use ($response) {
                return $response;
            });
        }

        $this->assertContains('Original Content:', $response->getContent());
        $this->assertContains('Test-1', $response->getContent());
        $this->assertContains('Test-2', $response->getContent());
        $this->assertContains('Test-3', $response->getContent());
        $this->assertContains('Original Content:<h1>Test-1</h1><h1>Test-2</h1><h1>Test-3</h1>', $response->getContent());
    }
}
