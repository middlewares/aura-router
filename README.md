# middlewares/aura-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to use [Aura.Router](https://github.com/auraphp/Aura.Router/) and store the route handler in a request attribute.

## Requirements

* PHP >= 7.0
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http message implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
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

## Options

#### `__construct(Aura\Router\RouterContainer $router)`

The router instance to use. 

#### `attribute(string $attribute)`

The name of the server request attribute used to save the handler. The default value is `request-handler`.

#### `responseFactory(Psr\Http\Message\ResponseFactoryInterface $responseFactory)`

A PSR-17 factory to create the error responses (`404`, `405`, `406`, etc).

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/aura-router.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/middlewares/aura-router/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/g/middlewares/aura-router.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/aura-router.svg?style=flat-square
[ico-sensiolabs]: https://img.shields.io/sensiolabs/i/3409cd4b-666d-4d3d-ba3b-1861f3b610f1.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/aura-router
[link-travis]: https://travis-ci.org/middlewares/aura-router
[link-scrutinizer]: https://scrutinizer-ci.com/g/middlewares/aura-router
[link-downloads]: https://packagist.org/packages/middlewares/aura-router
[link-sensiolabs]: https://insight.sensiolabs.com/projects/3409cd4b-666d-4d3d-ba3b-1861f3b610f1
