<?php
namespace ntentan\views\helpers\minifiables\minifiers\js\jshrink;
use ntentan\views\helpers\minifiables\Minifier;

class JshrinkMinifier extends Minifier
{
    public function performMinification($script)
    {
        $minified = \JShrink\Minifier::minify(
            $script, array('flaggedComments' => false)
        );
        return $minified;
    }
}
