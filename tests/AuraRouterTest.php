<?php

namespace Middlewares\Tests;

use Middlewares\AuraRouter;
use Middlewares\Utils\Dispatcher;
use Middlewares\Utils\Factory;
use Aura\Router\RouterContainer;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testAuraRouter()
    {
        $router = new RouterContainer();
        $map = $router->getMap();

        $map->get('index', '/user/{name}/{id}', function ($request, $letter1, $letter2) {
            $this->assertEquals('oscarotero', $request->getAttribute('name'));
            $this->assertEquals('35', $request->getAttribute('id'));

            echo $letter1;

            return $letter2;
        });

        $dispatcher = new Dispatcher([
            (new AuraRouter($router))->arguments('O', 'k'),
        ]);

        $request = Factory::createServerRequest([], 'GET', 'http://domain.com/user/oscarotero/35');
        $response = $dispatcher->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('Ok', (string) $response->getBody());

        $request = Factory::createServerRequest([], 'POST', 'http://domain.com/user/oscarotero/35');
        $response = $dispatcher->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(405, $response->getStatusCode());

        $request = Factory::createServerRequest([], 'GET', 'http://domain.com/not-found');
        $response = $dispatcher->dispatch($request);

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
