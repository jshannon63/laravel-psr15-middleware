<?php

namespace Jshannon63\Psr15Middleware;

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class exampleMiddleware implements MiddlewareInterface
{
    protected $message;

    public function __construct($parm1 = 'Hello', $parm2 = 'World')
    {
        $this->message = $parm1.' '.$parm2;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // process any request manipulations here before the handler.
        // remember that only "before" middlewares have access to
        // the request object before the application acts on it.
        // the handler will ensure the next middleware will see any
        // changes to the request object.

        $response = $handler->handle($request);

        // response actions go here after the handler provides
        // you with a response object. keep in mind that any
        // "before" middlewares will only have access to a mock
        // response object and any updates will be lost.

        $response->getBody()->rewind();
        $body = $response->getBody();
        $contents = $body->getContents();
        $contents = str_replace(
                '<body>',
                "<body>\n\t<h1>".$this->message.'</h1>',
                $contents
            );
        $body->rewind();
        $body->write($contents);

        // return the reponse object here.
        // "terminable" middlewares run after the response has
        // been sent back to the browser. they will receive the
        // request object passed into this method and will get
        // a copy of the  response object from the handler.

        return $response->withBody($body);
    }
}
