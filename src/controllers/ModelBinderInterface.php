<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\controllers;

use ntentan\Controller;

/**
 * Description of ModelBinderInterface
 *
 * @author ekow
 */
interface ModelBinderInterface
{
    public function bind(Controller $controller, $type);
    public function getBound();
}
