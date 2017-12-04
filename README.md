


# Use your PSR-15 compliant middleware in Laravel

##### NOTE: A simple "PSR-7 Only" compliant middleware package is available [here](https://github.com/jshannon63/psr7middleware).

##### What it does and why:
The laravel-psr15-middleware package (a.k.a. Psr15Middleware) is a Laravel 
compatible middleware that creates an abstraction between Laravel's Foundation 
HTTP message objects and middleware stack, allowing the injection of an 
additional PSR-15 interface middleware stack using PSR-7 Message objects.
  
Once installed, you will be able to run PSR-15 compatible middlewares within
the Psr15Middleware stack integrated with Laravel's own middleware stack. All 
PSR-15 middleware is run entirely within the PSR15Middleware subsystem. The two 
stacks are run separately and sequentially (Laravel then PSR-15). It should be 
noted, that Psr15Middleware clones the Laravel's Response and Request objects and 
then converts them to PSR-7 objects during this process. This means that PSR-15 
middlewares  will not have access to Laravel specific properties and methods 
within the PSR-7 Response and Request objects.
    
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
```
composer require jshannon63/laravel-psr15-middleware  
```
```
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
## Usage

##### Add your PSR-15 compliant middlewares to the /config/psr15middleware.php configuration file.
1. It is NOT necessary to declare PSR-15 middleware in the app/Http/Middleware/Kernel.php file. 
Psr15Middleware will automatically register itself and its middlewares by pushing them onto the Laravel 
middleware stacks.
2. Config entries can be classnames, callables or objects as shown in the example below.
3. Additional sections for aliases ($routeMiddleware) and groups ($middlewareGroups) which closely
adheres to the special route middleware groups within the app\Http\Middleware\Kernel.php file.
4. You can add new groups if you like (i.e., custom as shown).
```
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
        'minify' => (new \Middlewares\Minifier('html'))
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
  
        // process request/response obects here
        $response->getBody()->write("<h1>PSR-15 Middleware Rocks!</h1>");
  
        return $response;
    }
}

```

## Execution Flow
  
Laravel will begin execution of the middelware stack according to the order of 
definition within the Kernel.php file. Once the Laravel's middleware dispatcher
reaches the Psr15Middleware class, Laravel will be forced to complete all the
Foudation middlewares before executing the first PSR-15 middleware. Once the final
PSR-15 middleware is executed, a Foundation response object will be returned to
the Laravel middleware dispatcher.
  
Please note that the PSR-7 Message objects are immutable. Psr15Middleware will work
with cloned/converted Illuminate Response/Request objects. The updated clone of the 
Response object is returned to to the Laravel middleware system at the completion of 
the stack execution.
  
## Middleware Sources

For some PSR-15 middleware to use, take a look at [middlewares/psr15middlewares](https://github.com/middlewares/psr15-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md