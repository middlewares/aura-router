<?php
declare(strict_types = 1);

namespace Middlewares;

use Aura\Router\RouterContainer;
use Middlewares\Utils\Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuraRouter implements MiddlewareInterface
{
    /**
     * @var RouterContainer The router container
     */
    private $router;

    /**
     * @var string Attribute name for handler reference
     */
    private $attribute = 'request-handler';

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Set the RouterContainer instance.
     */
    public function __construct(RouterContainer $router, ResponseFactoryInterface $responseFactory = null)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory ?: Factory::getResponseFactory();
    }

    /**
     * Set the attribute name to store handler reference.
     */
    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Process a server request and return a response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $matcher = $this->router->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();

            switch ($failedRoute->failedRule) {
                case 'Aura\Router\Rule\Allows':
                    return $this->responseFactory
                        ->createResponse(405)
                        ->withHeader('Allow', implode(', ', $failedRoute->allows)); // 405 METHOD NOT ALLOWED
                case 'Aura\Router\Rule\Accepts':
                    return $this->responseFactory
                        ->createResponse(406); // 406 NOT ACCEPTABLE
                case 'Aura\Router\Rule\Host':
                case 'Aura\Router\Rule\Path':
                    return $this->responseFactory
                        ->createResponse(404); // 404 NOT FOUND
            }

            return $this->responseFactory->createResponse(500); // 500 INTERNAL SERVER ERROR
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute($this->attribute, $route->handler);

        return $handler->handle($request);
    }
}
