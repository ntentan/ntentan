<?php
namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\honam\Templates;

/**
 * Creates an instance of the View class and sets the appropriate template and layouts for binding in action methods.
 * 
 * @author ekow
 */
class ViewBinder implements ModelBinderInterface
{
    private $templates;
    private $home;

    public function __construct(Templates $templates, string $home)
    {
        $this->templates = $templates;
        $this->home = $home;
    }

    #[\Override]
    public function bind(array $data) 
    {
        $className = strtolower($data["route"]["controller"]); 
        $action = strtolower($data["route"]["action"]); 
        $this->templates->prependPath("{$this->home}/views/{$className}");
        $instance = $data["instance"];
//        if ($instance->getTemplate() == null) {
        $instance->setTemplate("{$className}_{$action}.tpl.php");
//        }
        return $instance;
    }

    #[\Override]
    public function getRequirements(): array {
        return ["instance", "route"];
    }
}
