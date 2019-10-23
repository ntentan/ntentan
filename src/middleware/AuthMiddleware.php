<?php

/**
 * Source file for the auth component
 *
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category Components
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\middleware;

use ntentan\Session;
use ntentan\AbstractMiddleware;
use ntentan\middleware\auth\AuthMethodFactory;

/**
 * AuthComponent provides a simplified authentication scheme
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AuthMiddleware extends AbstractMiddleware
{
    private $authenticated;
    private $authMethodFactory;

    public function __construct(AuthMethodFactory $authMethodFactory)
    {
        $this->authenticated = Session::get('logged_in');
        $this->authMethodFactory = $authMethodFactory;
    }

    public static function registerAuthMethod($authMethod, $class)
    {
        self::$authMethods[$authMethod] = $class;
    }

    private function getAuthMethod()
    {
        $authMethod = $this->authMethodFactory->createAuthMethod($this->getParameters() ?? ['']);
        $authMethod->setParameters($this->getParameters()); // 2 factory
        return $authMethod;
    }

    public function run($route, $response)
    {
        if (Session::get('logged_in')) {
            return $this->next($route, $response);
        }

        $parameters = $this->getParameters();
        $excluded = $parameters->get('excluded', []);
        if (in_array($route['route'], $excluded)) {
            return $this->next($route, $response);
        }

        $response = $this->getAuthMethod()->login($route);
        if ($response == false) {
            return $this->next($route, $response);
        } else {
            return $response;
        }
    }
}
