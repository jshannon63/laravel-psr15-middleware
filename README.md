


# Use your PSR-15 compliant middleware in Laravel

#### What it does and why:
PHP-FIG standards related to the HHTP Message Interface (PSR-7) have been in place for some time now. The standard for HTTP Handlers (PSR-15) is approved as of Jan 22, 2018. A proposal related to HTTP Message Factories (PSR-17) is being actively developed.

Laravel already provides a pathway for obtaining PSR-7 request objects from route closures or controller methods. Laravel also allows returning PSR-7 response objects from a route or controller. However, having a new PSR related to middleware doesn't necessarily mean that Laravel is going to implement a compliant middleware stack. Using a bridge (like this package) is a perfectly acceptable way to adopt PSR-15 standards within Laravel without completely changing the underlying structure of the existing framework. 

I acknowledge that middlewares are a simple thing and should be thin and easily managed/created, I also believe that there is a great deal of value in re-usable web components that can be shared between frameworks which adhere to PSR standards. Many PSR-15 middleware components already exist in the PHP community and having them available to use in Laravel can be a definite benefit. That is why this package was created.
  
The laravel-psr15-middleware library (a.k.a. Psr15Middleware) is a Laravel compatible middleware that creates an abstraction (or bridge) between PSR-7/PSR-15 interfaces and Laravel's middleware stack and Foundation HTTP message objects.
  
Once installed, you will be able to run compliant PSR-15 middleware in Laravel using this package's integration in the existing Laravel middleware stack.
  
#### PSR implementation reasoning. TL;DR
This package fully implements the PSR-7 (psr/http-message) message object interfaces. The interface is realized through Zend Diactoros concrete implementations of both the Request and Response objects. It also fully implements the proposed PSR-15 (psr/http-server-middleware) middleware and (psr/http-server-handler) request handler interfaces. However, it does not yet include the proposed PSR-17 (http-factory) factory interfaces for creating PSR-7 objects. This is due to the fact that we use the Symfony PSR-7 Bridge to make the conversions in both directions between Foundation and PSR-7 message objects.
  
## Installation
Within your Laravel project folder, install this package using composer. If you are using Laravel 5.5 or later, service provider registration will happen automatically.
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
That's it! Now you can configure and run your PSR-15 middleware. The default configuration in `/config/psr15middleware.php` comes with the exampleMiddleware enabled for demonstration purposes. You will need to disable all of the examples and add your own middleware classes as described below.
## Usage

#### Add your PSR-15 compliant middlewares to the /config/psr15middleware.php configuration file.
1. It is NOT necessary to declare PSR-15 middleware in the `app/Http/Middleware/Kernel.php` file as is normally done. Psr15Middleware will automatically register itself and its middlewares by pushing them onto the Laravel middleware stack.
2. Config entries are arrays and can can contain classnames, callables or objects as shown in the example below. Each entry has two additional parameters which follow the middleware declaration:
    * "prepend" or "append" will determine if your midleware will be placed at
    the head or tail of the middleware stack.
    * "before", "after" and "terminable" specify the type of middleware. Before middlewares run before the application acts on the request. After middlewares run after the request has been acted on by the application, but before the response has been sent to the browser. Terminable middlewares run after the browser has received the response and are generally used for housekeeping task which require access to the request and/or response objects. 
3. Additional sections for aliases ($routeMiddleware) and groups ($middlewareGroups) which closely adhere to the special route middleware groups within the `app\Http\Middleware\Kernel.php` file.
4. You can add new groups if you like (i.e., custom as shown).
5. Constructor arguments can be passed for middlewares declared as callables or objects within the configuration. All PSR-15 middleware constructors will be treated as variadic functions and therefore will be able to accept any number of arguments to their constructor. Note: These constructor arguments can also be passed as Laravel middleware route parameters. See the Laravel documentation for more on this feature.


```php
<?php

return [
    'middleware' => [
        [\Jshannon63\Psr15Middleware\exampleMiddleware::class, 'append', 'before'],
        [
            function() {
                return new \Jshannon63\Psr15Middleware\exampleMiddleware('Lovin', 'Laravel');
            },
            'prepend',
            'after'
        ],
        [
            (new \Jshannon63\Psr15Middleware\exampleMiddleware('PSR-15','Rocks')),
            'append',
            'after'
        ]
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
        'psr15' => [
            (new \Jshannon63\Psr15Middleware\exampleMiddleware('Aliased','Middleware')),
            'prepend',
            'after'
        ]
    ]
];

```
##### Your PSR-15 compatible middleware must have the following signature:
```php

// your namespace here

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class exampleMiddleware implements MiddlewareInterface
{
    // Your constructor will be treated as a variadic function
    // and parameters may be passed either as a middleware route
    // parameter or as defined in the /config/psr15middleware.php
    // config file. You can read more about middleware parameters
    // in the Laravel documentation.

    public function __construct()
    {
        // if needed
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

        // "terminable" middlewares are run after the response has
        // been sent back to the browser. they will receive the
        // request object passed into this method and will get
        // a copy of the response object from the handler.

        // return the reponse object here.

        return $response;
    }
}

```

## Execution Flow
  
All PSR-15 middleware is encapsulated and managed entirely by the PSR15Middleware subsystem. On boot, Psr15Middleware will bind a wrapper object for each PSR-15 middleware to make it appear native to Laravel. Then the objects will be placed into the Laravel middleware stack according to your configuration parameters. The middlewares themselves will only be instanitated as needed. Laravel will execute the middelwares according to the system priorites and as modified during registration of PSR-15 middlewares by Psr15Middleware.
  
Also, keep in mind, that since Psr15Middlware operates on PSR-7 message objects, PSR-15 middlewares will not have access to Laravel/Symfony specific properties and methods of the Foundation message objects.
  
## Middleware Sources

For some PSR-15 middleware to use, take a look at [middlewares/psr15middlewares](https://github.com/middlewares/psr15-middlewares).
  
## Contributing

If you would like to contribute refer to CONTRIBUTING.md