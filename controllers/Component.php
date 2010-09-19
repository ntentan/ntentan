<?php
/**
 * The class file which contains the Component class
 *
 * LICENSE:
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
 *
 * @package    ntentan
 * @author     James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @copyright  2010 James Ekow Abaka Ainooson
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */


namespace ntentan\controllers\components;

use ntentan\controllers\Controller;
use ntentan\Ntentan;

/**
 * The base class for all Componets. Components are little plugins which could
 * be written to extend the functionality of Controllers. Components basically
 * provide action methods which extend the capability of any Controller into
 * which it is loaded. Components can set variables in their parent controller
 * through which they can directly interract with the view.
 *  
 * @author James Ekow Abaka Ainooson
 * @abstract
 */
abstract class Component extends Controller
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
     * Sets the controller path of this component.
     * @param $controllerPath
     */
    public function setControllerPath($controllerPath)
    {
        $this->path = $controllerPath;
    }

    public function set($params1, $params2)
    {
        $this->controller->set($params1, $params2);
    }

    public function get()
    {
        return $this->controller->get();
    }

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
    
    public function useTemplate($file)
    {
        $templateFile = $this->controller->filePath . $file;
        if(file_exists($templateFile))
        {
            $this->view->template = $templateFile;
        }
        else
        {
            $this->view->template = $this->filePath . "/$file";
        }
    }
}
