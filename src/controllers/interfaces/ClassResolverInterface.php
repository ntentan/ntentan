<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\controllers\interfaces;

/**
 * Description of ClassNameResolverInterface
 *
 * @author ekow
 */
interface ClassResolverInterface
{
    public function getControllerClassName($name);
}
