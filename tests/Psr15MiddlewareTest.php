<?php

declare(strict_types=1);

namespace Tests;

require_once __DIR__.'/../src/Dispatcher.php';
require_once __DIR__.'/../src/Handler.php';

use Jshannon63\Psr15Middleware\Psr15Middleware;
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

    public function setUp(): void
    {
        parent::setUp();
        $this->middleware = [
            'psr15middleware.middleware' => [
                [exampleMiddleware1::class, 'append', 'after'],
                [function () {
                    return new exampleMiddleware2();
                }, 'append', 'after'],
                [(new exampleMiddleware3()), 'append', 'after'],
                [(new exampleMiddleware4()), 'append', 'after']
            ]
        ];
        $this->container = new Container;
        $this->config = new Repository($this->middleware);
    }

    public function test_middleware_stack(): void
    {
        $request = Request::create('http://localhost:8888/test/1', 'GET', [], [], [], [], null);
        $response = new Response('Original Content:', Response::HTTP_OK, ['content-type' => 'text/html']);

        foreach ($this->config->get('psr15middleware.middleware') as $middleware) {
            $psr15middleware = new Psr15Middleware($middleware[0], $middleware[2]);
            $response = $psr15middleware->handle($request, static function () use ($response) {
                return $response;
            });
        }

        $this->assertStringContainsString('Original Content:', $response->getContent());
        $this->assertStringContainsString('Test-1', $response->getContent());
        $this->assertStringContainsString('Test-2', $response->getContent());
        $this->assertStringContainsString('Test-3', $response->getContent());
        $this->assertStringContainsString('Original Content:<h1>Test-1</h1><h1>Test-2</h1><h1>Test-3</h1>', $response->getContent());
    }
}
