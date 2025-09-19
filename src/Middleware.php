<?php

namespace ntentan;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Defines the interface for middleware components.
 *
 * @author ekow
 */
interface Middleware {

    /**
     * Executes the middleware logic.
     *
     * @param ServerRequestInterface $request The incoming server request.
     * @param ResponseInterface $response The outgoing response.
     * @param callable $next The next middleware in the chain.
     *
     * @return ResponseInterface The modified response after middleware processing.
     */
    function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface;

    /**
     * Configures the middleware with the provided settings.
     *
     * @param array $configuration An associative array of configuration settings.
     */
    function configure(array $configuration);
}
