<?php

namespace ntentan;

use ntentan\utils\Input;

/**
 * Sets headers for redirecting.
 * To perform a redirection, a view can return an instance of this class. Utility methods exist in the Controller
 * class to generate Redirect objects which are automatically initialized with the URL of the controller. A redirect
 * URL query passed with the current URL overrides any URL passed to this class.
 *
 * @author ekow
 */
class Redirect
{
    /**
     * URL to redirect to.
     * 
     * @var string
     */
    private $url;

    /**
     * Create new instance of Redirect.
     * @param string $url The target URL of the redirection.
     */
    public function __construct($url)
    {
        $this->url = Input::get("redirect") ?? $url ;
    }

    public function __toString()
    {
        header("Location: {$this->url}");
        return "Redirecting to {$this->url} ...";
    }

    public function toAction($action)
    {
        $this->url .= "/{$action}";
        return $this;
    }
}
