<?php
declare(strict_types = 1);

namespace Middlewares\AuraRouter;

use Aura\Router\Route;
use Psr\Http\Message\ResponseInterface;

interface FailResponderInterface
{
    public function respond(Route $route): ResponseInterface;
}
