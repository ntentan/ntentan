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
    public function bind(Controller $controller, $type, $name, $instance = null)
    {
        return new UploadedFile($_FILES[$name]);
    }
    
    public function requiresInstance() : bool
    {
        return false;
    }
}
