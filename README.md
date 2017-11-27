


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

##### Add Psr15Middleware to app/Http/Middleware/Kernel.php
```php
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\TrustProxies::class,
        \App\Http\Middleware\Psr15Middleware::class,
    ];

```
##### Your PSR-7 middleware callable must have the following signature:
```php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
  
class exampleMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response,  callable $next = null){
        if ($next) {
            $response = $next($request, $response);
        }
  
        // middleware work is done here 
  
        return $response;
    }
}
```
##### Add each of your PSR-7 middleware classes to app/Http/Middleware/Psr7Middleware.php
```php

namespace App\Http\Middleware;
  
use Jshannon63\Psr7Middleware\Psr7Middleware as Middleware;
  
class Psr7Middleware extends Middleware
{
  
    protected $middleware = [
  
        \Jshannon63\Psr7Middleware\exampleMiddleware::class,
  
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

For some middleware to use, take a look at [Relay.Middleware](https://github.com/relayphp/Relay.Middleware) and [oscarotero/psr7-middlewares](https://github.com/oscarotero/psr7-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md