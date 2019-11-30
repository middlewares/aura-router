<?php
declare(strict_types = 1);

namespace Middlewares\Tests;

use Aura\Router\RouterContainer;
use Middlewares\AuraRouter;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class AuraRouterTest extends TestCase
{
    public function testAuraRouterNotFound()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $response = Dispatcher::run(
            [
                new AuraRouter($router),
            ],
            Factory::createServerRequest('GET', '/posts')
        );

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testResponseFactory()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $response = Dispatcher::run(
            [
                new AuraRouter($router, new Psr17Factory()),
            ],
            Factory::createServerRequest('GET', '/posts')
        );

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testAuraRouterNotAllowed()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers')->allows(['POST']);

        $response = Dispatcher::run(
            [
                new AuraRouter($router),
            ],
            Factory::createServerRequest('DELETE', '/users')
        );

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, POST', $response->getHeaderLine('Allow'));
    }

    public function testAuraRouterNotAccepted()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers')->accepts(['application/json']);

        $response = Dispatcher::run(
            [
                new AuraRouter($router),
            ],
            Factory::createServerRequest('GET', '/users')->withHeader('Accept', 'text/html')
        );

        $this->assertEquals(406, $response->getStatusCode());
    }

    public function testAuraRouterOK()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $response = Dispatcher::run(
            [
                new AuraRouter($router),
                function ($request) {
                    echo $request->getAttribute('request-handler');
                },
            ],
            Factory::createServerRequest('GET', '/users')
        );

        $this->assertEquals('listUsers', (string) $response->getBody());
    }

    public function testAuraRouterCustomAttribute()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $response = Dispatcher::run(
            [
                (new AuraRouter($router))->attribute('handler'),
                function ($request) {
                    echo $request->getAttribute('handler');
                },
            ],
            Factory::createServerRequest('GET', '/users')
        );

        $this->assertEquals('listUsers', (string) $response->getBody());
    }

    public function testAuraRouterAttributes()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users/{name}', 'getUser');

        $response = Dispatcher::run(
            [
                new AuraRouter($router),
                function ($request) {
                    echo $request->getAttribute('name');
                },
            ],
            Factory::createServerRequest('GET', '/users/alice')
        );

        $this->assertEquals('alice', (string) $response->getBody());
    }
}
