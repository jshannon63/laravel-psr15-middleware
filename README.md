


# Use your PSR-15 compliant middleware in Laravel

##### NOTE: A simple PSR-7 only compliant middleware package is available [here](https://github.com/jshannon63/psr7middleware).

Laravel uses the Symfony HTTPFoundation Request and Response objects.
These along with the format of the Laravel middleware stack makes
it impossible to take advantage of the many useful PSR-15 compliant
middleware packages that are available.
  
laravel-psr15-middleware (Psr15Middleware) is a Laravel compatible middleware that creates an abstraction 
between the Foundation objects of Laravel's Middleware stack and the more widely
accepted PSR-15 Middleware interface.


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
1. It is NOT necessary to declare PSR-15 middleware in the app/Http/Middleware/Kernel.php file. Psr15Middleware will automatically register middlewares by pushing them on the various middleware stacks of Laravel.
2. Config entries can be classnames, callables or objects as shown in the example below.
3. Additional sections for aliases ($routeMiddleware) and groups ($middlewareGroups) which equate to 
the special route middleware groups within the app\Http\Middleware\Kernel.php file.
4. You can add new groups if you like (i.e., custom as shown).
```
return [
    'middleware' => [
        \Jshannon63\Psr15Middleware\exampleMiddleware::class,
        function() {
            return new \Jshannon63\Psr15Middleware\exampleMiddleware();
         },
        (new \Jshannon63\Psr15Middleware\exampleMiddleware())
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
  
Laravel will begin execution of the middelware stack according to the 
order of definition within the Kernel.php file. Once the middleware dispatcher
reaches a PSR-15 middleware class, Laravel will be forced to complete all the
Foudation middlewares before executing the first PSR-7 middleware. Once the final
PSR-7 middleware is executed, a Foundation response object will be returned.
  
## Middleware Sources

For some PSR-15 middleware to use, take a look at [middlewares/psr15middlewares](https://github.com/middlewares/psr15-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md