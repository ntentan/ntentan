<?php
namespace ntentan\views\helpers\minifiables\minifiers\js\packer;

use ntentan\views\helpers\minifiables\Minifier;

class PackerMinifier extends Minifier
{
    public function performMinification($script)
    {
        require_once "third_party/jspacker/class.JavaScriptPacker.php";
        $packer = new \JavaScriptPacker($script);
        return $packer->pack();
    }
}
