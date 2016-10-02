<?php

namespace Middlewares\Tests;

use Middlewares\AuraRouter;
use Zend\Diactoros\ServerRequest;
use mindplay\middleman\Dispatcher;
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

        $response = $dispatcher->dispatch(new ServerRequest([], [], 'http://domain.com/user/oscarotero/35'));

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals('Ok', (string) $response->getBody());

        $response = $dispatcher->dispatch(new ServerRequest([], [], 'http://domain.com/user/oscarotero/35', 'post'));

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(405, $response->getStatusCode());

        $response = $dispatcher->dispatch(new ServerRequest([], [], 'http://domain.com/not-found'));

        $this->assertInstanceOf('Psr\\Http\\Message\\ResponseInterface', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
