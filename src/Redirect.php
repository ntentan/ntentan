<?php

namespace ntentan;

use ntentan\interfaces\RenderableInterface;
use ntentan\utils\Input;

/**
 * Sets headers for redirecting.
 * To perform a redirection, a view can return an instance of this class. Utility methods exist in the Controller
 * class to generate Redirect objects which are automatically initialized with the URL of the controller. A redirect
 * URL query passed with the current URL overrides any URL passed to this class.
 *
 * @author ekow
 */
class Redirect implements RenderableInterface
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

    /**
     * Sets the header to redirect and returns a message to the user.
     *
     * @return string
     */
    public function __toString()
    {
        header("Location: {$this->url}");
        return "Redirecting to {$this->url} ...";
    }

    /**
     * For redirect initialized with a controller url, this method modifies redirect to an action.
     *
     * @param $action
     * @return $this
     */
    public function toAction($action)
    {
        $this->url .= ($this->url[-1] === '/' ? '' : '/') . "{$action}";
        return $this;
    }
}
