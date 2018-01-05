


# Use your PSR-15 compliant middleware in Laravel

##### What it does and why:
The laravel-psr15-middleware package (a.k.a. Psr15Middleware) is a Laravel 
compatible middleware that creates an abstraction between PSR-7/PSR-15 
interfaces and Laravel's middleware stack and Foundation HTTP message objects.
  
Once installed, you will be able to run PSR-15 compatible middleware in Laravel
using this package's integration in the existing middleware stack.
  
##### PSR implementation reasoning. TL;DR
This package fully implements the PSR-7 (psr/http-message) message object interfaces. 
The interface is realized through Zend Diactoros concrete implementations of both the 
Request and Response objects. It also fully implements the proposed PSR-15 
(http-interop/http-server-middleware) middleware and (http-interop/http-server-handler) 
request handler interfaces. However, it does not yet include the proposed PSR-17 
(http-factory) factory interfaces for creating PSR-7 objects. This is due to the fact 
that we use the Symfony PSR-7 Bridge to make the conversions in both directions between 
HTTPFoundation and PSR-7 message objects. I may find myself at a later time preparing 
a http-factory-symfony-bridge implementation of the PSR-17 standard.
  
## Installation
Within your Laravel project folder, install this package using composer. You will not 
need to register the service provider. Laravel ^5.5 will handle that for you.
```bash
composer require jshannon63/laravel-psr15-middleware  
```
Then use artisan to publish the package's configuration assets
```bash
php artisan vendor:publish
  
Which provider or tag's files would you like to publish?:
  [0] Publish files from all providers and tags listed below
  [1] Provider: Fideloper\Proxy\TrustedProxyServiceProvider
  [2] Provider: Illuminate\Mail\MailServiceProvider
  [3] Provider: Illuminate\Notifications\NotificationServiceProvider
  [4] Provider: Illuminate\Pagination\PaginationServiceProvider
  [5] Provider: Jshannon63\Psr15Middleware\Psr15MiddlewareServiceProvider
  [6] Tag: laravel-mail
  [7] Tag: laravel-notifications
  [8] Tag: laravel-pagination
 >
 
 choose the Psr15MiddlewareServiceProvider
```
That's it! Now you can configure and run your PSR-15 middleware. The default configuration comes with
the exampleMiddleware enabled for demonstration purposes. You will need to disable the examples and 
add your own middleware classes as described below.
## Usage

##### Add your PSR-15 compliant middlewares to the /config/psr15middleware.php configuration file.
1. It is NOT necessary to declare PSR-15 middleware in the app/Http/Middleware/Kernel.php file. 
Psr15Middleware will automatically register itself and its middlewares by pushing them onto the Laravel 
middleware stacks.
2. Config entries can be classnames, callables or objects as shown in the example below.
3. Additional sections for aliases ($routeMiddleware) and groups ($middlewareGroups) which closely
adheres to the special route middleware groups within the app\Http\Middleware\Kernel.php file.
4. You can add new groups if you like (i.e., custom as shown).
```php
return [
    'middleware' => [
      
        \Jshannon63\Psr15Middleware\exampleMiddleware::class,
  
        function() {
            return new \Jshannon63\Psr15Middleware\exampleMiddleware();
         },
           
        (new \Jshannon63\Psr15Middleware\exampleMiddleware()),
    
    ],
    'groups' => [
       'web' => [
  

        ],
        'api' => [
  

        ],
        'custom' => [
  
        ],
    ],
    'aliases' => [
        'psr15' => (new \Jshannon63\Psr15Middleware\exampleMiddleware())
    ]
];

```
##### Your PSR-15 compatible middleware must have the following signature:
```php

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
  
class exampleMiddleware implements MiddlewareInterface
{
    public function __construct()
    {
        // if needed
    }
  
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
  
        /**** Your middleware functionality begins here ****/
        
            $response->getBody()->rewind();
            $body = $response->getBody();
            $contents = $body->getContents();
            $contents = str_replace(
                "<body>",
                "<body>\n\t<h1>PSR-15 Middleware Rocks!</h1>",
                $contents
            );
            $body->rewind();
            $body->write($contents);
        
        /**** and ends here ****/

  
        return $response;
    }
}

```

## Execution Flow
  
All PSR-15 middleware is run entirely within the PSR15Middleware subsystem. The two 
stacks are run separately and sequentially (Laravel then PSR-15). Laravel will begin 
execution of the middelware stack according to the order of definition within the 
Kernel.php file. Once the Laravel's middleware dispatcher reaches the Psr15Middleware 
class, Laravel will be forced to complete all the Foudation middlewares before 
executing the first PSR-15 middleware. Once the final PSR-15 middleware is executed, 
a Foundation response object will be returned to the Laravel middleware dispatcher.
  
Please note that the PSR-7 Message objects are immutable. Psr15Middleware will work
with cloned/converted Illuminate Response/Request objects. The updated clone of the 
Response object is returned to to the Laravel middleware system at the completion of 
the stack execution. This also means that PSR-15 middlewares will not have access to 
Laravel/Symfony specific properties and methods on the Foundation message objects.
  
## Middleware Sources

For some PSR-15 middleware to use, take a look at [middlewares/psr15middlewares](https://github.com/middlewares/psr15-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md