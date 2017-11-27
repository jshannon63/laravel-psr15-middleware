


# Psr15Middleware - Use your PSR-15 compliant middleware in Laravel

##### NOTE: A PSR-7 only compliant version of this package is available.

Laravel uses the Symfony HTTPFoundation Request and Response objects.
These along with the format of the Laravel middleware stack makes
it impossible to take advantage of the many useful PSR-15 compliant
middleware packages that are available.
  
Psr15Middleware is a Laravel compatible middleware that creates an abstraction 
between the Foundation objects of Laravel's Middleware stack and the more widely
accepted PSR-15 Middleware interface.


## Installation
```
composer require jshannon63/psr15middleware  
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

##### Add one or more of the three available Psr15Middleware classes to app/Http/Middleware/Kernel.php as shown below.
```php
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\Psr15Middleware::class,
    ];
    
    // or if used in a middleware group
    
        protected $middlewareGroups = [
            'web' => [
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                // \Illuminate\Session\Middleware\AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \App\Http\Middleware\Psr15GroupMiddleware::class,

            ],
    
            'api' => [
                'throttle:60,1',
                'bindings',
            ],
        ];
        
    // or if used in routes
    
        protected $routeMiddleware = [
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'psr15' => \App\Http\Middleware\Psr15RouteMiddleware::class,
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
##### Add each of your PSR-15 middleware classes to the appropriate Psr15Middleware class. Either app/Http/Middleware/Psr15Middleware.php, app/Http/Middleware/Psr15GroupMiddleware.php, app/Http/Middleware/Psr15RouteMiddleware.php
```php

namespace App\Http\Middleware;
  
use Jshannon63\Psr15Middleware\Psr15Middleware as Middleware;
  
class Psr15Middleware extends Middleware
{
  
    protected $middleware = [
  
        \Jshannon63\Psr15Middleware\exampleMiddleware::class,
  
    ];
  
}

```
  
## Execution Flow
  
Laravel will begin execution of the middelware stack according to the 
order of definition within the Kernel.php file. Once the middleware dispatcher
reaches the Psr7Middleware class, Laravel will be forced to complete all the
Foudation middlewares before executing the first PSR-7 middleware. Once the final
PSR-7 middleware is executed, a Foundation response object will be returned.
  
## Middleware Sources

For some PSR-15 middleware to use, take a look at [middlewares/psr15middlewares](https://github.com/middlewares/psr15-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md