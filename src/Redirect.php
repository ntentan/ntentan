<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan;

/**
 * Description of Redirect
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
