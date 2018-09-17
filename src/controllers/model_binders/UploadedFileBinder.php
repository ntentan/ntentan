<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\utils\filesystem\UploadedFile;
use ntentan\Controller;

/**
 *
 * @author ekow
 */
class UploadedFileBinder implements ModelBinderInterface
{
    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance = null)
    {
        return isset($_FILES[$name]) ? new UploadedFile($_FILES[$name]) : null;
    }
    
    public function requiresInstance() : bool
    {
        return false;
    }
}
