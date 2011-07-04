<?php
namespace ntentan\views\helpers\minifiables\minifiers\js\jsminplus;

use ntentan\views\helpers\minifiables\Minifier;

class JsminplusMinifier extends Minifier
{
    public function performMinification($script)
    {
        require_once "third_party/jsminplus/jsminplus.php";
        $minified = \JSMinPlus::minify($script);
        return $minified;
    }
}
