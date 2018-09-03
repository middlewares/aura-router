<?php
declare(strict_types = 1);

namespace Middlewares;

use Aura\Router\RouterContainer;
use Middlewares\Utils\Traits\HasResponseFactory;
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
     * @var FailResponderInterface Responder for failed to match route
     */
    private $fail;

    /**
     * Set the RouterContainer instance.
     */
    public function __construct(RouterContainer $router, AuraRouter\FailResponderInterface $fail = null)
    {
        $this->router = $router;
        $this->fail = $fail ?: new AuraRouter\FailResponder();
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
            return $this->fail->respond($failedRoute);
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $request->withAttribute($this->attribute, $route->handler);

        return $handler->handle($request);
    }
}
