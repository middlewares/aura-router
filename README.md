# middlewares/aura-router

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-scrutinizer]][link-scrutinizer]
[![Total Downloads][ico-downloads]][link-downloads]
[![SensioLabs Insight][ico-sensiolabs]][link-sensiolabs]

Middleware to use [Aura.Router](https://github.com/auraphp/Aura.Router/).

## Requirements

* PHP >= 5.6
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http mesage implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15](https://github.com/http-interop/http-middleware) middleware dispatcher ([Middleman](https://github.com/mindplay-dk/middleman), etc...)

## Installation

This package is installable and autoloadable via Composer as [middlewares/aura-router](https://packagist.org/packages/middlewares/aura-router).

```sh
composer require middlewares/aura-router
```

## Example

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
	new Middlewares\AuraRouter($router)
]);

$response = $dispatcher->dispatch(new ServerRequest('/hello/world'));
```

**Aura.Router** allows to define anything as the router handler (a closure, callback, action object, controller class, etc). By default, it's resolved in the following way:

* If it's a string similar to `Namespace\Class::method`, and the method is not static, create a instance of `Namespace\Class` and call the method.
* If the string is the name of a existing class (like: `Namespace\Class`) and contains the method `__invoke`, create a instance and execute that method.
* Otherwise, treat it as a callable.

If you want to change this behaviour, use a container implementing the [container-interop](https://github.com/container-interop/container-interop) to return the route callable.

## Options

#### `__construct(Aura\Router\RouterContainer $router)`

The router instance to use. 

#### `resolver(Interop\Container\ContainerInterface $resolver)`

To use a container implementing [container-interop](https://github.com/container-interop/container-interop) to resolve the route handlers.

#### `arguments(...$args)`

Extra arguments to pass to the controller. This is useful to inject, for example a service container:

```php
$map->get('post', '/posts/{id}', function ($request, $app) {
    $id = $request->getAttribute('id');
    $post = $app->get('database')->select($id);
    
    return $app->get('templates')->render($post);
});

$dispatcher = new Dispatcher([
    (new Middlewares\AuraRouter($router))
        ->arguments($app)
]);

```

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
