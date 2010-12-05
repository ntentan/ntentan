<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\controllers\components;

use ntentan\controllers\Controller;
use ntentan\Ntentan;
use \ReflectionMethod;

/**
 * The base class for all Componets. Components are little plugins which are
 * written to extend the functionality of Controllers. Components basically
 * provide extra pre defined action methods which extend the capability of any 
 * Controller into which it is loaded. Components can set variables in their 
 * parent controller through which they can directly interract with the views 
 * and layouts.
 * 
 * Since components are subclasses of controllers, they have access to all the
 * utility methods which are available to controllers.
 * 
 * @author James Ekow Abaka Ainooson
 */
class Component extends Controller
{
    /**
     * The name of the controller this instance of the comonent is attached to.
     * @var string
     */
    protected $controllerName;
    
    /**
     * An instance of the controller this instance of the component is attached
     * to.
     * @var Controller
     */
    protected $controller;

    /**
     * Dummy constructor.
     */
    public function __construct()
    {
        
    }

    /**
     * Sets the controller instance of this component.
     * @param Controller $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Sets the controller name of this component.
     * @param string $controllerName
     */
    public function setControllerName($controllerName)
    {
        $this->controllerName = $controllerName;
    }

    /**
     * Sets the controller route of this component.
     * @param $controllerRoute
     */
    public function setControllerRoute($controllerRoute)
    {
        $this->route = $controllerRoute;
    }

    /**
     * (non-PHPdoc)
     * @see controllers/ntentan\controllers.Controller::set()
     */
    public function set($params1, $params2)
    {
        $this->controller->set($params1, $params2);
    }

    /**
     * (non-PHPdoc)
     * @see controllers/ntentan\controllers.Controller::get()
     */
    public function get()
    {
        return $this->controller->get();
    }

    /**
     * Calls a method from the controller to which this component is attached.
     */
    protected function callControllerMethod()
    {
        $arguments = func_get_args();
        $method = array_shift($arguments);
        if(method_exists($this->controller, $method))
        {
            $reflectionMethod = new ReflectionMethod($this->controller, $method);
            $ret = $reflectionMethod->invokeArgs($this->controller, $arguments);
        }
        return $ret;
    }
    
    /**
     * Execute a callback method
     */
    protected function executeCallbackMethod()
    {
        $arguments = func_get_args();
        $method = array_shift($arguments);
        if(method_exists($this->controller, $method))
        {
            $reflectionMethod = new ReflectionMethod($this->controller, $method);
            $reflectionMethod->invokeArgs($this->controller, $arguments);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * (non-PHPdoc)
     * @see controllers/ntentan\controllers.Controller::__get()
     */
    public function __get($property)
    {
        switch ($property)
        {
            case "view":
                return $this->controller->view;//Instance;
                break;

            default:
                return parent::__get($property);
        }
    }
    
    /**
     * Selects a template be used for rendering the output. This method allows
     * the component to use template files found within its directory to render
     * the output. If for some reason the controller using the component has a
     * template file with the same name, the file found in the controller is
     * used instead. In this way a mechanism is provided where the default templates
     * provided with the components could be overidden by those in the controller.
     * Note that all the views rendered by the component are also rendered using
     * the View class.
     *  
     * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
     * @param $file
     */
    public function useTemplate($file)
    {
        $templateFile = $this->controller->filePath . $file;
        if(file_exists($templateFile))
        {
            $this->view->template = $templateFile;
        }
        else
        {
            $this->view->template = $this->filePath . "/templates/$file";
        }
    }

    /**
     * Specifies a layout to be used by the component. This method allows the
     * component to override or provide a layout to be used for the rendering
     * of the HTML code. The layout is first searched for in the components
     * directory if it is not found then the applications layout directory is
     * looked up.
     * 
     * @param string $file
     */
    public function useLayout($file)
    {
        $layoutFile = Ntentan::$layoutsPath . $file;
        if(file_exists($layoutFile))
        {
            $this->view->layoutFile = $layoutFile;
        }
        else
        {
            $this->view->layoutFile = $this->filePath . "/layouts/$file";
        }
    }
}
