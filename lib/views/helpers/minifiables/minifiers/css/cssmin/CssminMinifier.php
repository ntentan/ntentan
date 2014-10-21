<?php
namespace ntentan\views\helpers\minifiables\minifiers\css\cssmin;
use ntentan\views\helpers\minifiables\Minifier;

class CssminMinifier extends Minifier
{
    public function performMinification($script)
    {
        return \CssMin::minify($script);
    }
}
