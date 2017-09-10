<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\Controller;
use ntentan\honam\TemplateEngine;
use ntentan\panie\Container;

/**
 * Creates an instance of \ntentan\View and binds it to parameters in action methods.
 * 
 * @author ekow
 */
class ViewBinder implements ModelBinderInterface
{
    private $bound = false;

    public function bind(Controller $controller, Container $serviceLocator, $action, $type, $name)
    {
        $view = $serviceLocator->resolve($type);
        $className = strtolower(substr((new \ReflectionClass($controller))->getShortName(), 0, -10));
        TemplateEngine::prependPath("views/{$className}");
        if ($view->getTemplate() == null) {
            $view->setTemplate("{$className}_{$action}.tpl.php");
        }
        return $view;
    }

    public function getBound()
    {
        return $this->bound;
    }
}
