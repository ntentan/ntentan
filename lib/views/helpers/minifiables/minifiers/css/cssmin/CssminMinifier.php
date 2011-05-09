<?php
namespace ntentan\views\helpers\minifiables\minifiers\css\cssmin;

use ntentan\views\helpers\minifiables\Minifier;

class CssminMinifier extends Minifier
{
    public function performMinification($script)
    {
        require_once "third_party/cssmin/cssmin.php";
        return \CssMin::minify($script);
    }
}
