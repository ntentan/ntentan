<?php

namespace ntentan\middleware\auth;

use ntentan\utils\Input;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ntentan\http\StringStream;

/**
 * An authentication method that receives a username and password through an HTTP request.
 * The parameters which should be sent through a POST request are retrieved and validated against a local auth database.
 */
class HttpRequestAuthMethod implements AuthMethod
{
    use LocalPassword;
    
    private array $config;
    
    #[\Override]
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $usernameField = $this->config['username_field'] ?? "username";
        $passwordField = $this->config['password_field'] ?? "password";

        if (Input::exists(Input::POST, $usernameField) && Input::exists(Input::POST, $passwordField)) {
            $username = Input::post($usernameField);
            if ($this->verify($username, Input::post($passwordField))) {
                return $this->getRedirect()->toUrl($this->config['success_redirect']);
            } else {
                return false;
            }
        }
        
        if(!in_array($request->getUri()->getPath(), $this->config['excluded'])) {
            return $response->withAddedHeader("Location", $this->config['login_path'])->withBody(StringStream::empty());
        }
    }

    #[\Override]
    public function setup(array $config): void 
    {
        $this->config = $config;
    }
}
