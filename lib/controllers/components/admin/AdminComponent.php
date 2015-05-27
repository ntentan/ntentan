<?php
/**
 * Source file for the admin component
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


namespace ntentan\controllers\components\admin;

use ntentan\Ntentan;
use ntentan\controllers\components\Component;
use ntentan\models\Model;
use ntentan\exceptions\MethodNotFoundException;
use ntentan\honam\TemplateEngine;
use ntentan\utils\Text;

use \ReflectionMethod;


/**
 * Admin component provides an interface through which data in a model could be
 * manipulated. Adding this component to any controller automatically provides
 * the interface for adding, editing and deleting items in the model class found
 * within the attached controllers package or namespace. Apart from the manipulation
 * interface, the admin component also provides a full blown admin console site.
 *
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class AdminComponent extends Component
{
    /**
     * An array which holds a list of all the fields which would be displayed in
     * the list.
     * @var array
     */
    public $listFields = array();
    
    public $listConditions = array();

    /**
     * A list of extra operations
     * @var array
     */
    public $extraOperations = array();

    /**
     * A custom prefix to automatically append to the beginning of all URLS
     * @var string
     */
    public $prefix;

    /**
     * Whether the admin component is in console mode or not. In console mode,
     * the admin component provides a full blown admin console site which could
     * be used as the basis to build an admin section for any site or web
     * application.
     * @var boolean
     */
    public $consoleMode = false;

    /**
     * The route to use for redirections when in console mode.
     * @var string
     */
    public $consoleModeRoute;

    /**
     * A structured array which is used to describe the side menu when the
     * admin component is in console mode.
     * @var array
     */
    protected $sections = array();
    
    /**
     * An instance of the model whose data this component is going to be operating
     * on
     * @var Model
     */
    public $model;
    
    /**
     * A flag for the headings. If true the headings on top of the various sections
     * such as 'Add new ...' or 'Edit ...' would be generated and  displayed by 
     * the system,
     * @var boolean
     */
    public $headings = true;
    
    /**
     * The level of the 'h' tags used in rendering the headings. The default 
     * value of 2 causes the headings to be rendered with the <h2> tags
     * @var integer
     */
    public $headingLevel = '2';
    
    /**
     * A flag to determine whether notifications should be shown for the various
     * actions or not.
     * @var boolean
     */
    public $notifications = true;
    
    /**
     * An array which holds all the operations which can be perfomed on particuar
     * models. These operations are usually the little links which are found
     * next to the items listed in the views (such as Delete, Edit, etc).
     * @var array
     */
    private $operations = array();
    
    /**
     * The path to the template file which should be used for the rendering
     * of the model operations.
     * @var type 
     */
    public $operationsTemplate;
    
    /**
     * The default operation which would be executed when the whole row is clicked.
     * By default it is configured to invoke the edit operation.
     * @var type 
     */
    public $rowOperation;
    
    /**
     * The template used to render the rows for the views.
     */
    public $rowTemplate;
    
    /**
     * Determines whether the current view should have the edit operation.
     * @var boolean
     */
    public $hasEditOperation = true;
    
    /**
     * Determines whether the current view should have the edit operation.
     * @var boolean
     */
    public $hasAddOperation = true;
    
    /**
     * The name of the entity currently being operated on by the component.
     * @var string
     */
    public $entity;
    
    /**
     * A codified form of the entity name
     * @var string
     */
     public $entityCode;

    public function init()
    {
        TemplateEngine::appendPath(Ntentan::getFilePath('lib/controllers/components/admin/views/layouts'));
        TemplateEngine::appendPath(Ntentan::getFilePath('lib/controllers/components/admin/views/templates'));
    }

    public function addOperation($operation)
    {
        if(is_string($operation))
        {
            $operation = array(
                'label' => \ucwords(str_replace('_', ' ', $operation)),
                'operation' => $operation
            );
        }

        if(!isset($operation["controller"]))
        {
            $operation["controller"] =
                $this->consoleMode ?
                    $this->consoleModeRoute :
                    $this->controller->route;
        }

        $this->operations[$operation["operation"]] = array(
            "label" => $operation["label"],
            "link" =>
                $operation["confirm_message"] == "" ?
                Ntentan::getUrl("{$this->prefix}/{$operation["controller"]}/{$operation["operation"]}/") :
                Ntentan::getUrl("{$this->prefix}/{$operation["controller"]}/confirm/{$operation["operation"]}/"),
            "confirm_message" => $operation["confirm_message"]
        );
    }

    private function setupOperations()
    {
        if($this->hasAddOperation)
        {
            $this->addOperation(
                array(
                    "label" => "Edit",
                    "operation" => "edit"
                )
            );
        }

        if($this->hasEditOperation)
        {
            $this->addOperation(
                array(
                    "label"=> "Delete",
                    "operation" => "delete",
                    "confirm_message" => "Are you sure you want to delete <b>%item%</b>?"
                )
            );
        }
    }

    public function page($pageNumber)
    {
        //execute the page enxtension method
        if($this->consoleMode)
        {
            $pageExtensionMethodName = Text::ucamelize(Ntentan::plural($this->entity),".","", true) . 'AdminPage';
            if(method_exists($this->controller, $pageExtensionMethodName))
            {
                $pageExtensionMethod = new ReflectionMethod($this->controller, $pageExtensionMethodName);
                $pageExtensionMethod->invoke($this->controller, $pageNumber);
            }
            $pageControllerRoute = $this->consoleModeRoute;
        }
        else
        {
            $pageControllerRoute = $this->controller->route;
            $this->entity = Ntentan::singular($this->controller->model->getName());
            $this->entityCode = str_replace(' ', '_', $this->entity);
        }
        

        $this->setupOperations();
        $this->operationsTemplate = $this->operationsTemplate == null ? 'operations.tpl.php': $this->operationsTemplate;

        $this->set("operations_template", $this->operationsTemplate);
        $this->set("row_template", $this->rowTemplate);
        $this->set("entity", \ucwords(Ntentan::plural($this->entity)));
        $this->set("notifications", $this->notifications);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("item_operation_url", Ntentan::getUrl($this->consoleModeRoute. '/edit'));
        $this->set('route', $pageControllerRoute);
        $entityCode = str_replace(' ', '_', $this->entity);
        
        $itemsPerPage = 10;
        $model = $this->getModel();
        
        $this->view->template = "{$entityCode}_page.tpl.php";
        $listFields = $this->listFields;
        if(count($listFields) == null)
        {
            $listFields = array();
            $description = $model->describe();
            foreach($description['fields'] as $field)
            {
                if(array_search($field['name'], $description['primary_key']) !== false) continue;
                $listFields[] = $field['name'];
            }
        }
        $listFields[] = "id";

        $data = $model->get(
            $itemsPerPage,
            array(
                "fields"            =>  $listFields,
                "offset"            =>  ($pageNumber-1) * $itemsPerPage,
                "sort"              =>  $model->getName() . ".id desc",
                "fetch_belongs_to"  =>  true,
                'conditions'        =>  $this->listConditions
            )
        );

        $count = $model->get('count');
        //$data = $data->toArray();      
        $listData = array();
        
        foreach($data as $datum)
        {
            foreach($listFields as $listField)
            {
                $fieldSegments = explode('.', $listField);
                $listDataRow[$listField] = $datum[$listField];
            }
            $listDataRow['id'] = $datum['id'];
            $listData[] = $listDataRow;
        }
        
        if(count($listData) > 0)
        {
            $headers = array_keys($listData[0]);
            array_pop($headers);
        }
        
        $headers[] = '';
        $this->set("data", $listData);
        $this->set("headers", $headers);
        $numPages = intval(ceil($count / $itemsPerPage));
        $pagingLinks = array();

        $this->set("operations", $this->operations);
        $this->set("list_fields", $this->listFields);
        $this->set("notification_type", $_GET["n"]);
        $this->set("notification_item", base64_decode($_GET["i"]));

        if($count > $itemsPerPage)
        {
            $this->set(
                array(
                    'pagination' => true,
                    'page_number' => $pageNumber,
                    'number_of_pages' => $numPages,
                    'base_route' => "{$this->prefix}/$pageControllerRoute/page/"
                )
            );
        }
    }

    public function addSection($section)
    {
        if(is_string($section))
        {
            $array = explode('.', str_replace('/', '.', $section));
            $newSection = array(
                'route' => $section,
                'label' => \ucwords(str_replace(array('/', '_'), array(' ', ' '), $section)),
                'model' => str_replace('/', '.', $section),
                'entity' => Ntentan::singular(str_replace('_', ' ', end($array)))
            );
            $section = $newSection;
        }
        $this->sections[$section['route']] = $section;
        return $this;
    }
    
    public function setupConsoleView()
    {
        $this->view->layout = 'admin.tpl.php';
        $this->set("app_name", Ntentan::$config['application']['name']);
        $this->set("stylesheet", Ntentan::getFilePath("lib/controllers/components/admin/assets/css/admin.css"));
        
        $profile = $this->authComponent->getProfile();
        $this->set('username', $profile['username']);
        
        $this->headingLevel = '3';

        //Setup the menus to be used in this administrator section
        $menuItems = array();
        foreach($this->sections as $section)
        {
            if($section['type'] == 'custom')
            {
                $item['label'] = $section['label'];
                $item['url'] = Ntentan::getUrl($this->controller->route . "/{$section['route']}");
            }
            else
            {
                $item['label'] = $section['label'];
                $item['url'] = Ntentan::getUrl($this->controller->route . "/console/{$section['route']}");
            }
            $menuItems[] = $item;
        }
        
        $this->set('sections_menu', $menuItems);        
    }

    public function console()
    {
        $this->setupConsoleView();
        $this->view->template = 'console.tpl.php';
        
        $arguments = func_get_args();
        if(count($arguments) == 0)
        {
            // Run a default here
        }
        else if(end($arguments) == "add")
        {
            array_pop($arguments);
            $sectionkey = implode(".", $arguments);
            $this->model = Model::load($this->sections[$sectionkey]['model']);
            $this->entity = $this->sections[$sectionkey]['entity'];
            $this->consoleModeRoute = "{$this->prefix}{$this->controller->route}/console/{$this->sections[$sectionkey]['route']}";
            $this->add();
        }
        else if(is_numeric(end($arguments)))
        {
            $index = array_pop($arguments);
            $action = array_pop($arguments);
            if(end($arguments) == "confirm")
            {
                array_pop($arguments);
                $sectionkey = implode(".", $arguments);
                $this->model = Model::load($this->sections[$sectionkey]['model']);
                $this->entity = $this->sections[$sectionkey]['entity'];
                $this->consoleModeRoute = "{$this->prefix}{$this->controller->route}/console/{$this->sections[$sectionkey]['route']}";
                $this->confirm($action, $index);
            }
            else
            {
                $sectionkey = implode(".", $arguments);
                $this->model = Model::load($this->sections[$sectionkey]['model']);
                $this->entity = $this->sections[$sectionkey]['entity'];
                $this->consoleModeRoute = "{$this->prefix}{$this->controller->route}/console/{$this->sections[$sectionkey]['route']}";
                switch($action)
                {
                    case "edit":
                        $this->edit($index);
                        break;
                    case "delete":
                        $this->delete($index);
                        break;
                    case 'page':
                        $this->page($index);
                        break;
                    default:
                        $extensionMethodName = Text::ucamelize(Ntentan::plural($this->entity),".","", true) . 'Admin' . Text::ucamelize($action);
                        if(method_exists($this->controller, $extensionMethodName))
                        {
                            $this->view->template = "{$this->entity}_{$action}.tpl.php";
                            $extensionMethod = new ReflectionMethod($this->controller, $extensionMethodName);
                            $extensionMethod->invoke($this->controller, $index);
                        }
                        else
                        {
                            throw new MethodNotFoundException("Could not find $extensionMethodName method in the admin controller");
                        }
                        break;
                }
            }
        }
        else
        {
            $sectionkey = implode(".", $arguments);
            $this->model = Model::load($this->sections[$sectionkey]['model']);
            $this->entity = $this->sections[$sectionkey]['entity'];
            $this->consoleModeRoute = "{$this->prefix}{$this->controller->route}/console/{$this->sections[$sectionkey]['route']}";
            $this->page(1);
        }
    }

    public function run()
    {
        if($this->consoleMode)
        {
            $this->console();
        }
        else
        {
            $this->page(1);
        }
    }

    public function confirm($operation, $id)
    {
        $this->setupOperations();
        $this->view->template = "confirm.tpl.php";
        $item = $this->getModel()->getFirstWithId($id);
        $this->set("item", (string)$item);
        $this->set("message", $this->operations[$operation]["confirm_message"]);
        $this->set("heading_level", $this->headingLevel);
        $route = $this->consoleMode ? $this->consoleModeRoute : $this->controller->route;
        $this->set("positive_route", Ntentan::getUrl("$route/$operation/$id"));
        $this->set("negative_route", Ntentan::getUrl($route));
    }

    public function delete($id)
    {
        $this->view = false;
        $item = $this->getModel()->getFirstWithId($id);
        $item->delete();
        if($this->consoleMode)
        {
            $route = $this->consoleModeRoute;
        }
        else
        {
            $route = $this->route;
        }
        Ntentan::redirect(
            "$route?n=3&i=" . base64_encode($item)
        );
    }

    public function edit($id)
    {
        $this->view->template = "admin_component_edit.tpl.php";
        $description = $this->getModel()->describe();
        $this->set("fields", $description["fields"]);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $item = $this->getModel()->getFirstWithId($id);
        $data = $item->toArray();
        
        if($this->consoleMode)
        {
            $editExtensionMethodName = Text::ucamelize(Ntentan::plural($this->entity),".","", true) . 'AdminEdit';
            if(method_exists($this->controller, $editExtensionMethodName))
            {
                $editExtensionMethod = new ReflectionMethod($this->controller, $editExtensionMethodName);
                $editExtensionMethod->invoke($this->controller);
            }
        }
        
        $this->set("data", $data);
        $this->set("entity", $this->entity);
        $entityCode = str_replace(' ', '_', $this->entity);
        $this->set("entity_code", $entityCode);

        if(count($_POST) > 0)
        {
            $item->setData($_POST, true);
            $item->id = $id;
            if($item->update())
            {
                if($this->consoleMode)
                {
                    $route = $this->consoleModeRoute;
                }
                else
                {
                    $route = $this->route;
                }
                Ntentan::redirect(
                    "$route?n=2&i=" . base64_encode($item)
                );
            }
            else
            {
                $this->set('errors', $item->invalidFields);
            }
        }
    }
    
    public function add()
    {
        $model = $this->getModel();
        $description = $model->describe();
        
        $entityCode = str_replace(' ', '_', $this->entity);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("fields", $description["fields"]);
        $this->set('primary_key', $description['primary_key']);
        $this->set("entity", $this->entity);
        $this->set('entity_code', $entityCode);
        
        $this->view->template = "admin_component_add.tpl.php";
        
        if($this->consoleMode)
        {
            $addExtensionMethodName = Text::ucamelize(Ntentan::plural($entityCode),".","", true) . 'AdminAdd';
            if(method_exists($this->controller, $addExtensionMethodName))
            {
                $addExtensionMethod = new ReflectionMethod($this->controller, $addExtensionMethodName);
                $addExtensionMethod->invoke($this->controller);
            }
        }        
        
        if(count($_POST) > 0)
        {
            $model->setData($_POST);
            $id = $model->save();
            if($id > 0)
            {
                $route = $this->consoleMode ? $this->consoleModeRoute : $this->route;
                Ntentan::redirect(
                    "$route?n=1&i=" . base64_encode($model)
                );
            }
            else
            {
                $this->set("data", $_POST);
                $this->set("errors", $model->invalidFields);
            }
        }
    }

    private function getModel()
    {
        return is_object($this->model) ? $this->model : $this->controller->model;
    }

    public function getStylesheet()
    {
        return Ntentan::getFilePath('lib/controllers/components/admin/css/admin.css');
    }
}
