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
    private $bound = false;

    public function bind(Controller $controller, $action, $type, $name)
    {
        $this->bound = true;
        return new UploadedFile($_FILES[$name]);
    }

    public function getBound()
    {
        return $this->bound;
    }
}
