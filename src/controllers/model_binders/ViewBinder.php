<?php

namespace ntentan\controllers\model_binders;

use ntentan\Context;
use ntentan\controllers\ModelBinderInterface;
use ntentan\Controller;
use ntentan\honam\TemplateEngine;
use ntentan\honam\Templates;

/**
 * Creates an instance of the View class and sets the appropriate template and layouts for binding in action methods.
 * 
 * @author ekow
 */
class ViewBinder implements ModelBinderInterface
{
    private $templates;

    public function __construct(Templates $templates)
    {
        $this->templates = $templates;
    }

    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance=null)
    {
        $className = strtolower(substr((new \ReflectionClass($controller))->getShortName(), 0, -10));
        $action = $controller->getActionMethod();
        $this->templates->prependPath(APP_HOME . "views/{$className}");
        if ($instance->getTemplate() == null) {
            $instance->setTemplate("{$className}_{$action}.tpl.php");
        }
        return $instance;
    }

    public function requiresInstance() : bool
    {
        return true;
    }
}
