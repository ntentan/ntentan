<?php


namespace ntentan\middleware\auth;


use ntentan\Redirect;

trait Redirects
{
    protected $redirect;

    public function setRedirect(Redirect $redirect)
    {
        $this->redirect = $redirect;
    }
}