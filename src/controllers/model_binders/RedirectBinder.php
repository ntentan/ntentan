<?php

namespace ntentan\controllers\model_binders;


use ntentan\controllers\ModelBinderInterface;
use ntentan\Controller;
use ntentan\Redirect;

class RedirectBinder implements ModelBinderInterface
{
    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance=null)
    {
        return new Redirect("/{$parameters['controller']}");
    }

    public function requiresInstance(): bool
    {
        return false;
    }
}
