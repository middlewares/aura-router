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
     * The router container.
     *
     * @var RouterContainer
     */
    protected $router;

    /**
     * Response factory to customize the response.
     *
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Attribute name for handler reference.
     * This is used to get the handler of the selected route using $request->getAttribute($handlerAttribute)
     *
     * @var string
     */
    protected $handlerAttribute = 'request-handler';

    /**
     * Attribute name for route instance.
     * This is used to get the selected route instance using $request->getAttribute($routeAttribute)
     *
     * @var string
     */
    protected $routeAttribute = 'route';

    /**
     * Set the RouterContainer instance.
     */
    public function __construct(RouterContainer $router, ?ResponseFactoryInterface $responseFactory = null)
    {
        $this->router = $router;
        $this->responseFactory = $responseFactory ?: Factory::getResponseFactory();
    }

    /**
     * Set the attribute name to store handler reference.
     *
     * @deprecated Use handler() instead
     * @see handlerAttribute()
     */
    public function attribute(string $attribute): self
    {
        $this->handlerAttribute = $attribute;

        return $this;
    }

    /**
     * Set the attribute name to store handler reference.
     */
    public function handlerAttribute(string $handlerAttribute): self
    {
        $this->handlerAttribute = $handlerAttribute;

        return $this;
    }

    /**
     * Set the attribute name to store the route instance.
     */
    public function routeAttribute(string $routeAttribute): self
    {
        $this->routeAttribute = $routeAttribute;

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

            if (!$failedRoute) {
                return $this->responseFactory->createResponse(500); // 500 INTERNAL SERVER ERROR
            }

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

        $request = $request->withAttribute($this->handlerAttribute, $route->handler)
            ->withAttribute($this->routeAttribute, $route);

        return $handler->handle($request);
    }
}
