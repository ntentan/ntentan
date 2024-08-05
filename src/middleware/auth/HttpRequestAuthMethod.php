<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use ntentan\Session;
use ntentan\http\Redirect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ntentan\exceptions\NtentanException;

/**
 * An authentication method that receives a username and password through an HTTP request.
 * The parameters which should be sent through a POST request are retrieved and validated against a local auth database.
 */
class HttpRequestAuthMethod implements AuthMethod
{
    private Redirect $redirect;
    
    public function __construct(Redirect $redirect)
    {
        $this->redirect = $redirect;
    }
    
    /**
     * The internal configuration for the authentication method.
     * @var array
     */
    private array $config;

    #[\Override]
    public function configure(array $config): void 
    {
        $this->config = $config;
    }

    #[\Override]
    public function run(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        // Skip requests made to the login path so the underlying middleware can present the challenge.
        if ($request->getUri()->getPath() == $this->config['login_path'] && (strtolower($request->getMethod()) == "get")) {
            return $next($request, $response, $next);
        }
        if ($request->getUri()->getPath() != $this->config['login_path']) {
            return $this->redirect->to($this->config['login_path']);
        }
        $usernameField = $this->config['username_field'] ?? "username";
        $passwordField = $this->config['password_field'] ?? "password";

        if (isset($this->config['verify_passwords_with']) && Input::exists(Input::POST, $usernameField)) {
            if ($this->config['verify_passwords_with'](Input::post($usernameField), Input::post($passwordField))) {
                Session::set("authenticated", true);
                return $this->redirect->to("/");
            } else {
                return $next($request, $response->withStatus(401, "Invalid username or password"), $next);
            }
        } else if (!isset($this->config['verify_passwords_with'])) {
            throw new NtentanException("A password verification function was not specified for the HTTP authentication method");
        }
    }

    #[\Override]
    public function isAuthenticated(): bool
    {
        return Session::get('authenticated') ?? false;
    }
}
