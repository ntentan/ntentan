<?php
namespace ntentan\minification\minifiers\css\cssmin;

use ntentan\minification\Minifier;

class CssminMinifier extends Minifier
{
    public function performMinification($script)
    {
        require_once "third_party/cssmin/cssmin.php";
        return \CssMin::minify($script);
    }
}
