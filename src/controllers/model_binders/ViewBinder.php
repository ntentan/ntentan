<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\View;
use ntentan\Controller;
use ntentan\Context;
use ntentan\honam\TemplateEngine;

/**
 * 
 * @author ekow
 */
class ViewBinder implements ModelBinderInterface {

    private $bound = false;
    private $container;
    
    public function __construct(Context $context) {
        $this->container = $context->getContainer();
    }

    public function bind(Controller $controller, $action, $type, $name) {
        $view = $this->container->resolve(View::class);
        $className = strtolower(substr((new \ReflectionClass($controller))->getShortName(), 0, -10));
        TemplateEngine::prependPath("views/{$className}");
        if ($view->getTemplate() == null) {
            $view->setTemplate("{$className}_{$action}.tpl.php");
        }        
        return $view;
    }

    public function getBound() {
        return $this->bound;
    }

}
