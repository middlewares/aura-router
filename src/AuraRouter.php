<?php

namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Interop\Http\Middleware\DelegateInterface;
use Aura\Router\RouterContainer;
use Aura\Router\Route;

class AuraRouter implements ServerMiddlewareInterface
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
     * @var ContainerInterface Used to resolve the controllers
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
     * @param ContainerInterface $resolver
     *
     * @return self
     */
    public function resolver(ContainerInterface $resolver)
    {
        $this->resolver = $resolver;

        return $this;
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
                    return Utils\Factory::createResponse(405); // 405 METHOD NOT ALLOWED

                case 'Aura\Router\Rule\Accepts':
                    return Utils\Factory::createResponse(406); // 406 NOT ACCEPTABLE

                default:
                    return Utils\Factory::createResponse(404); // 404 NOT FOUND
            }
        }

        foreach ($route->attributes as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        return $this->executeCallable($route->handler, $request);
    }

    /**
     * Resolves the target of the route and returns a callable.
     *
     * @param mixed $target
     * @param array $args
     *
     * @throws RuntimeException If the target is not callable
     *
     * @return callable
     */
    private function resolveCallable($target, array $args)
    {
        if (empty($target)) {
            throw new RuntimeException('No callable provided');
        }

        if ($this->resolver) {
            return $this->resolver->get($target);
        }

        if (is_string($target)) {
            //is a class "classname::method"
            if (strpos($target, '::') === false) {
                $class = $target;
                $method = '__invoke';
            } else {
                list($class, $method) = explode('::', $target, 2);
            }

            if (!class_exists($class)) {
                throw new RuntimeException("The class {$class} does not exists");
            }

            $fn = new \ReflectionMethod($class, $method);

            if (!$fn->isStatic()) {
                $class = new \ReflectionClass($class);
                $instance = $class->hasMethod('__construct') ? $class->newInstanceArgs($args) : $class->newInstance();
                $target = [$instance, $method];
            }
        }

        //if it's callable as is
        if (is_callable($target)) {
            return $target;
        }

        throw new RuntimeException('Invalid callable provided');
    }

    /**
     * Execute the callable.
     *
     * @param mixed            $target
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    private function executeCallable($target, ServerRequestInterface $request)
    {
        ob_start();
        $level = ob_get_level();

        try {
            $arguments = array_merge([$request], $this->arguments);
            $return = call_user_func_array($this->resolveCallable($target, $arguments), $arguments);

            if ($return instanceof ResponseInterface) {
                $response = $return;
                $return = '';
            } else {
                $response = Utils\Factory::createResponse();
            }

            $return = self::flush($level).$return;
            $body = $response->getBody();

            if ($return !== '' && $body->isWritable()) {
                $body->write($return);
            }

            return $response;
        } catch (\Exception $exception) {
            self::flush($level);
            throw $exception;
        }
    }

    /**
     * Return the output buffer.
     *
     * @param int $level
     *
     * @return string
     */
    private static function flush($level)
    {
        $output = '';

        while (ob_get_level() >= $level) {
            $output .= ob_get_clean();
        }

        return $output;
    }
}
