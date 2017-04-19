<?php

namespace Middlewares\Tests;

use Middlewares\AuraRouter;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Aura\Router\RouterContainer;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testAuraRouterNotFound()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $request = Factory::createServerRequest([], 'GET', '/posts');

        $response = Dispatcher::run([
            new AuraRouter($router),
        ], $request);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testAuraRouterNotAllowed()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers')->allows(['POST']);

        $request = Factory::createServerRequest([], 'DELETE', '/users');

        $response = Dispatcher::run([
            new AuraRouter($router),
        ], $request);

        $this->assertEquals(405, $response->getStatusCode());
        $this->assertEquals('GET, POST', $response->getHeaderLine('Allow'));
    }

    public function testAuraRouterOK()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $request = Factory::createServerRequest([], 'GET', '/users');

        $response = Dispatcher::run([
            new AuraRouter($router),
            function ($request) {
                echo $request->getAttribute('request-handler');
            }
        ], $request);

        $this->assertEquals('listUsers', (string) $response->getBody());
    }

    public function testAuraRouterCustomAttribute()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('list', '/users', 'listUsers');

        $request = Factory::createServerRequest([], 'GET', '/users');

        $response = Dispatcher::run([
            (new AuraRouter($router))->attribute('handler'),
            function ($request) {
                echo $request->getAttribute('handler');
            }
        ], $request);

        $this->assertEquals('listUsers', (string) $response->getBody());
    }
}
