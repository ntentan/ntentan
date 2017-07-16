<?php

namespace ntentan;

/**
 * Sets headers for redirecting.
 *
 * @author ekow
 */
class Redirect
{
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
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
