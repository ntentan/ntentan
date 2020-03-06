<?php

namespace ntentan\controllers\model_binders;

use ntentan\controllers\ModelBinderInterface;
use ntentan\utils\filesystem\UploadedFile;
use ntentan\Controller;
use ntentan\utils\Text;

/**
 *
 * @author ekow
 */
class UploadedFileBinder implements ModelBinderInterface
{
    public function bind(Controller $controller, string $type, string $name, array $parameters, $instance = null)
    {
        if(isset($_FILES[$name])) {
            return new UploadedFile($_FILES[$name]);
        } elseif (isset($_FILES[$decamelizedName = Text::deCamelize($name)])) {
            return new UploadedFile($_FILES[$decamelizedName]);
        }
        return null;
    }
    
    public function requiresInstance() : bool
    {
        return false;
    }
}
