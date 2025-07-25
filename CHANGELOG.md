# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [2.2.1] - 2025-07-10
### Fixed
- Rename 500 to 404 status code when no routes were defined.

Before, this would return 500 (no routes defined in RouteContainer). But not defining any route should just mean 404.

```php
$router = new RouterContainer();

$response = Dispatcher::run(
    [
        new AuraRouter($router),
    ],
    Factory::createServerRequest('GET', '/posts')
);

$this->assertEquals(404, $response->getStatusCode());
```

## [2.2.0] - 2025-05-31
### Added
- Support for new `routeAttribute($name)` option which sets the attribute name that will hold the resolved Route instance.

This allows setting parameters, `extras()` in our case, but it could also be that we just extended the Route class and we added new method or anything else that we would like to recover inside the Request Handler:

```php
$map->get('activities.get', '/activities', ListActivities::class)
    ->extras([
        'key' => 'value'
     ]);
```

And then we can access the Route instance like this:

```php
public function process(
    ServerRequestInterface $request,
    RequestHandlerInterface $handler
): ResponseInterface {
    /** @var Route $actualRoute The resolved Route instance */
    $route = $request->getAttribute('route');
    
    // "value"
    $route->extras['key'];
    
    // "activities.get"
    $route->name;
}
```

### Changed
- `attribute($name)` was used to set Request Handler's attribute name and the general feeling that it transmitted didn't fit anymore as there is now the `routeAttribute($name)` method. So it was moved to a more specific `handlerAttribute($name)` method.

### Deprecated
- The `attribute($name)` method is still there but marked as `@deprecated` so it will be removed in the next major release. Just use `handlerAttribute($name)` instead.

## [2.1.1] - 2025-03-21
### Fixed
- Added a check for failedRoute when false. This should be rare.

## [2.1.0] - 2025-03-16
### Added
- Support for PHP 8.1, 8.2, 8.3 and 8.4.

## [2.0.1] - 2020-12-02
### Added
- Support for PHP 8

## [2.0.0] - 2019-11-30
### Added
- Added a second argument to the constructor to define the `responseFactory`

### Removed
- Support for PHP 7.0 and 7.1
- The `responseFactory()` option. Use the second argument in the constructor.

## [1.1.0] - 2018-08-04
### Added
- PSR-17 support
- New option `responseFactory`

## [1.0.1] - 2018-02-06
### Fixed
- Return a 500 response, if the failed route rule is different to 404, 405 or 406. [#2](https://github.com/middlewares/aura-router/pull/2)

## [1.0.0] - 2018-01-24
### Added
- Improved testing and added code coverage reporting
- Added tests for PHP 7.2

### Changed
- Upgraded to the final version of PSR-15 `psr/http-server-middleware`

### Fixed
- Updated license year

## [0.9.0] - 2017-11-13
### Changed
- Replaced `http-interop/http-middleware` with  `http-interop/http-server-middleware`.

### Removed
- Removed support for PHP 5.x.

## [0.8.0] - 2017-09-21
### Changed
- Append `.dist` suffix to phpcs.xml and phpunit.xml files
- Changed the configuration of phpcs and php_cs
- Upgraded phpunit to the latest version and improved its config file
- Updated to `http-interop/http-middleware#0.5`

## [0.7.0] - 2017-04-20
### Changed
- Handlers are no longer executed, only passed as attribute references.

## [0.6.0] - 2017-04-13
### Added
- New option `container()` that works as a shortcut to use a PSR-11 container as a resolver.

### Changed
- The option `resolver()` accepts any instance of `Middlewares\Utils\CallableResolver\CallableResolverInterface`.

### Fixed
- The `405` response includes an `Allow` header with the allowed methods for the request.

## [0.5.0] - 2017-02-27
### Changed
- Replaced `container-interop` by `psr/container`

## [0.4.0] - 2017-02-05
### Changed
- Updated to `middlewares/utils#~0.9`
- Improved route target resolution

## [0.3.0] - 2016-12-26
### Changed
- Updated tests
- Updated to `http-interop/http-middleware#0.4`
- Updated `friendsofphp/php-cs-fixer#2.0`

## [0.2.0] - 2016-11-22
### Changed
- Updated to `http-interop/http-middleware#0.3`

## [0.1.1] - 2016-10-03
### Fixed
- Use `Middlewares\Utils\CallableHandler` to resolve and execute the routes handlers. This fixes some issues resolving the callables.

## [0.1.0] - 2016-10-02
First version

[2.2.1]: https://github.com/middlewares/aura-router/compare/v2.2.0...v2.2.1
[2.2.0]: https://github.com/middlewares/aura-router/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/middlewares/aura-router/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/middlewares/aura-router/compare/v2.0.1...v2.1.0
[2.0.1]: https://github.com/middlewares/aura-router/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/middlewares/aura-router/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/middlewares/aura-router/compare/v1.0.1...v1.1.0
[1.0.1]: https://github.com/middlewares/aura-router/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/middlewares/aura-router/compare/v0.9.0...v1.0.0
[0.9.0]: https://github.com/middlewares/aura-router/compare/v0.8.0...v0.9.0
[0.8.0]: https://github.com/middlewares/aura-router/compare/v0.7.0...v0.8.0
[0.7.0]: https://github.com/middlewares/aura-router/compare/v0.6.0...v0.7.0
[0.6.0]: https://github.com/middlewares/aura-router/compare/v0.5.0...v0.6.0
[0.5.0]: https://github.com/middlewares/aura-router/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/middlewares/aura-router/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/middlewares/aura-router/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/middlewares/aura-router/compare/v0.1.1...v0.2.0
[0.1.1]: https://github.com/middlewares/aura-router/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/middlewares/aura-router/releases/tag/v0.1.0
