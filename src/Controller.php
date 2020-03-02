<?php

/**
 * The Controller base class for the Ntentan framework
 *
 * Ntentan Framework
 * Copyright (c) 2008-2012 James Ekow Abaka Ainooson
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
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */

namespace ntentan;

/**
 * The Controller class represents the base class for all controllers that are
 * built for the ntentan framework. Controllers are used to direct the flow of
 * your application logic. They are stored in modules and they contain methods
 * which are called from the url. Parameters to the methods are also passed
 * through the URL. If a method is not specified, the default method is called.
 * The methods called by the controllers are expected to set data into variables
 * which are later rendered as output to the end user through views.
 *
 * @author  James Ekow Abaka Ainooson
 * @todo    There must be a controller interface to be satisfied by all controllers
 */
class Controller
{
    /**
     * The action method that this controller will execute.
     *
     * @var string
     */
    private $actionMethod;

    /**
     * The parameters that are sent to the action method.
     *
     * @var array
     */
    private $actionParameters;

    /**
     * The name of a model binder to use as default for this controller only.
     * This model binder overrides the global default model binder but can be overidden by a model binder on an action
     * method.
     * @var string
     */
    protected $defaultModelBinderClass;


//    /**
//     * Get an instance of the Redirect object that is setup with this controller as its base URL.
//     *
//     * @return Redirect
//     */
//    protected function getRedirect() : Redirect
//    {
//        $context = Context::getInstance();
//        $redirect = new Redirect($context->getUrl($context->getParameter('controller_path')));
//        return $redirect;
//    }
//
//    /**
//     * Returns a URL to an action in this controller.
//     *
//     * @param string $action The name of the action
//     * @return string A URL to the action
//     */
//    protected function getActionUrl($action) : string
//    {
//        $context = Context::getInstance();
//        return $context->getUrl($context->getParameter('controller_path') . $action);
//    }

    /**
     * Set the action method to be executed by this controller.
     *
     * @param string $actionMethod 
     * @return void
     */
    public function setActionMethod($actionMethod) : void
    {
        $this->actionMethod = $actionMethod;
    }

    /**
     * Set the parameters to be passed to the action method.
     *
     * @param array $actionParameters
     */
    public function setActionParameters(array $actionParameters) : void
    {
        $this->actionParameters = $actionParameters;
    }

    /**
     * Get the action method selected to be executed for this controller.
     * @return string
     */
    public function getActionMethod() : string
    {
        return $this->actionMethod;
    }

    /**
     * Get the parameters to be passed to the action method.
     * @return array
     */
    public function getActionParameters() : array
    {
        return $this->actionParameters;
    }

    /**
     * Return the default model binder class for this controller if any.
     * @return string
     */
    public function getDefaultModelBinderClass()
    {
        return $this->defaultModelBinderClass;
    }
}
