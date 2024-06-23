<?php

namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author ekow
 */
interface Middleware {
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;
}
