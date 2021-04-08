# middlewares/aura-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

Middleware to use [Aura.Router](https://github.com/auraphp/Aura.Router/) and store the route handler in a request attribute.

## Requirements

* PHP >= 7.2
* A [PSR-7 http library](https://github.com/middlewares/awesome-psr15-middlewares#psr-7-implementations)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/aura-router](https://packagist.org/packages/middlewares/aura-router).

```sh
composer require middlewares/aura-router
```

## Example

In this example, we are using [middleware/request-handler](https://github.com/middlewares/request-handler) to execute the route handler:

```php
//Create the router
$router = new Aura\Router\RouterContainer();

$map = $router->getMap();

$map->get('hello', '/hello/{name}', function ($request) {

    //The route parameters are stored as attributes
    $name = $request->getAttribute('name');

    //You can echo the output (it will be captured and writted into the body)
    echo sprintf('Hello %s', $name);

    //Or return a string
    return sprintf('Hello %s', $name);

    //Or return a response
    return new Response();
});

$dispatcher = new Dispatcher([
    new Middlewares\AuraRouter($router),
    new Middlewares\RequestHandler()
]);

$response = $dispatcher->dispatch(new ServerRequest('/hello/world'));
```

**Aura.Router** allows to define anything as the router handler (a closure, callback, action object, controller class, etc). The middleware will store this handler in a request attribute.

## Usage

Create the middleware with a `Aura\Router\RouterContainer` instance:

```php
$routerContainer = new Aura\Router\RouterContainer();

$route = new Middlewares\AuraRouter($routerContainer);
```

Optionally, you can provide a `Psr\Http\Message\ResponseFactoryInterface` as the second argument, that will be used to create the error responses (`404`, `405` or `406`). If it's not defined, [Middleware\Utils\Factory](https://github.com/middlewares/utils#factory) will be used to detect it automatically.

```php
$routerContainer = new Aura\Router\RouterContainer();
$responseFactory = new MyOwnResponseFactory();

$route = new Middlewares\AuraRouter($routerContainer, $responseFactory);
```

### attribute

The name of the server request attribute used to save the handler. The default value is `request-handler`.

```php
$dispatcher = new Dispatcher([
    //Save the route handler in an attribute called "route"
    (new Middlewares\AuraRouter($routerContainer))->attribute('route'),

    //Execute the route handler
    (new Middlewares\RequestHandler())->attribute('route')
]);
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/aura-router.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/aura-router/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/aura-router.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/aura-router
[link-downloads]: https://packagist.org/packages/middlewares/aura-router
