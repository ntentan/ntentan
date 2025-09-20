<?php
namespace ntentan\middleware\auth;

use ntentan\middleware\Middleware;
use ntentan\Session;
use ntentan\sessions\SessionStore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The Authentication middleware ensures that all requests are from properly authenticated sessions before sending
 * them down the pipeline. In cases where the request is not properly authenticated, it's passed on to an authentication
 * module for authentication.
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AuthMiddleware implements Middleware
{
    /**
     * A factory for creating authentication methods.
     * @var \ntentan\middleware\auth\AuthMethodFactory
     */
    private AuthMethodFactory $authMethodFactory;

    /**
     * An array holding the configuration for the authentication middleware.
     * @var array
     */
    private array $config;

    private SessionStore $session;

    /**
     * Create an instance of the authentication middleware.
     * @param AuthMethodFactory $authMethodFactory
     */
    public function __construct(AuthMethodFactory $authMethodFactory, SessionStore $sessionStore)
    {
        $this->authMethodFactory = $authMethodFactory;
        $this->session = $sessionStore;
    }
    
    /**
     * Chech for paths that are excluded from authentication.
     * @param string $path
     * @param array $excludedPaths
     * @return boolean
     */
    private function isExcluded(string $path, array $excludedPaths)
    {
        foreach ($excludedPaths as $excludedPath) {
            if (substr($path, 0, strlen($excludedPath)) == $excludedPath) {
                return true;
            }
        }
        return false;
    }

    /**
     * Executes the authentication middleware.
     */
    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        // First checks
        if ($this->session->get('authenticated') || $this->isExcluded($request->getUri()->getPath(), $this->config['excluded'] ?? [])) {
            return $next($request, $response);
        } 
        
        // Create instance and perform second checks
        $authMethod = $this->authMethodFactory->create($this->config);
        
        // Run the authentication middleware and proceed accordingly
        $authResponse = $authMethod->run($request, $response, $next);
        if ($authResponse === true) {
            return $next($request, $response);
        } else {
            return $authResponse;
        }
    }
    
    #[\Override]
    public function configure(array $configuration)
    {
        $this->config = $configuration;

    }
}

