<?php
namespace ntentan\http;

class Redirect extends Response
{
    private string $prefix;
    
    /**
     * Create a new redirect around the HTTP response methods.
     */
    public function __construct(Uri $uri) 
    {
        $this->prefix = $uri->getPrefix();
    }
    
    public function to(string $location): Redirect
    {
        return $this->withHeader("Location", $this->prefix . $location)->withStatus(303);
    }
}

