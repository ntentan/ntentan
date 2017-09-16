<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\interfaces;

use ntentan\AbstractMiddleware;

/**
 *
 * @author ekow
 */
interface MiddlewareFactoryInterface
{
    public function createMiddleware(array $parameters) : AbstractMiddleware;
}
