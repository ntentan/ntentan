<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\Controller;
use ntentan\Context;
use ntentan\honam\TemplateEngine;

/**
 * Creates an instance of a view and binds it to parameters in action methods.
 * 
 * @author ekow
 */
class ViewBinder implements ModelBinderInterface
{
    private $bound = false;
    private $container;
    
    public function __construct(Context $context)
    {
        $this->container = $context->getContainer();
    }

    public function bind(Controller $controller, $action, $type, $name)
    {
        $view = $this->container->resolve($type);
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
