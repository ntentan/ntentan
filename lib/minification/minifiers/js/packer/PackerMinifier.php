<?php
namespace ntentan\minification\minifiers\js\packer;

use ntentan\minification\Minifier;

class PackerMinifier extends Minifier
{
    public function performMinification($script)
    {
        require_once "third_party/jspacker/class.JavaScriptPacker.php";
        $packer = new \JavaScriptPacker($script);
        return $packer->pack();
    }
}
