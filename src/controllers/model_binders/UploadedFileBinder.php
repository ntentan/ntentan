<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;

/**
 * 
 * @author ekow
 */
class UploadedFileBinder implements ModelBinderInterface
{
    private $bound = false;
    
    public function bind(\ntentan\Controller $controller, $type, $name)
    {
        $this->bound = true;
        return new \ntentan\utils\filesystem\UploadedFile($_FILES[$name]);
    }

    public function getBound()
    {
        return $this->bound;
    }
}
