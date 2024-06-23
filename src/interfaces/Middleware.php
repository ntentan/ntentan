<?php
namespace ntentan\interfaces;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 * @author ekow
 */
interface Middleware
{
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
