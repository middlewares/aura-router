<?php
declare(strict_types = 1);

namespace Middlewares\AuraRouter;

use Aura\Router\Route;
use Aura\Router\Rule;
use Middlewares\Utils\Traits\HasResponseFactory;
use Psr\Http\Message\ResponseInterface;

class FailResponder implements FailResponderInterface
{
    use HasResponseFactory;

    /**
     * Respond to a failed route
     */
    public function respond(Route $route): ResponseInterface
    {
        switch ($route->failedRule) {
            case Rule\Allows::class:
                return $this->createResponse(405)
                    ->withHeader('Allow', implode(', ', $route->allows)); // 405 METHOD NOT ALLOWED

            case Rule\Accepts::class:
                return $this->createResponse(406); // 406 NOT ACCEPTABLE

            case Rule\Host::class:
            case Rule\Path::class:
                return $this->createResponse(404); // 404 NOT FOUND
            default:
                return $this->createResponse(500); // 500 INTERNAL SERVER ERROR
        }
    }
}
