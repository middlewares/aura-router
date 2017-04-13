<?php

namespace Middlewares;

use Middlewares\Utils\CallableHandler;
use Middlewares\Utils\Factory;
use Middlewares\Utils\CallableResolver\CallableResolverInterface;
use Middlewares\Utils\CallableResolver\ContainerResolver;
use Middlewares\Utils\CallableResolver\ReflectionResolver;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Aura\Router\RouterContainer;

class AuraRouter implements MiddlewareInterface
{
    /**
     * @var RouterContainer The router container
     */
    private $router;

    /**
     * @var array Extra arguments passed to the controller
     */
    private $arguments = [];

    /**
     * @var CallableResolverInterface|null Used to resolve the controllers
     */
    private $resolver;

    /**
     * Set the RouterContainer instance.
     *
     * @param RouterContainer $router
     */
    public function __construct(RouterContainer $router)
    {
        $this->router = $router;
    }

    /**
     * Set the resolver used to create the controllers.
     *
     * @param ContainerInterface $container
     *
     * @return self
     */
    public function resolver(CallableResolverInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Set the container used to create the controllers.
     *
     * @param ContainerInterface $container
     *
     * @return self
     */
    public function container(ContainerInterface $container)
    {
        return $this->resolver(new ContainerResolver($container));
    }

    /**
     * Extra arguments passed to the callable.
     *
     * @return self
     */
    public function arguments()
    {
        $this->arguments = func_get_args();

        return $this;
    }

    /**
     * Process a server request and return a response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $matcher = $this->router->getMatcher();
        $route = $matcher->match($request);

        if (!$route) {
            $failedRoute = $matcher->getFailedRoute();

            switch ($failedRoute->failedRule) {
                case 'Aura\Router\Rule\Allows':
                    return Factory::createResponse(405)->withHeader('Allow', implode(', ', $failedRoute->allows)); // 405 METHOD NOT ALLOWED

                case 'Aura\Router\Rule\Accepts':
                    return Factory::createResponse(406); // 406 NOT ACCEPTABLE

                default:
                    return Factory::createResponse(404); // 404 NOT FOUND
            }
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $arguments = array_merge([$request], $this->arguments);

        $callable = $this->getResolver()->resolve($route->handler, $arguments);

        return CallableHandler::execute($callable, $arguments);
    }

    /**
     * Return the resolver used for the controllers
     *
     * @return CallableResolverInterface
     */
    private function getResolver()
    {
        if (!isset($this->resolver)) {
            $this->resolver = new ReflectionResolver();
        }

        return $this->resolver;
    }
}
