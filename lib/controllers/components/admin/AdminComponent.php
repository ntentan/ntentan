<?php
/**
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

namespace ntentan\controllers\components\admin;

use ntentan\Ntentan;
use ntentan\controllers\components\Component;
use ntentan\models\Model;
use ntentan\utils\Janitor;
use ntentan\models\exceptions\MethodNotFoundException;
use ntentan\views\template_engines\TemplateEngine;

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

    /**
     * A list of extra operations
     * @var array
     */
    public $extraOperations = array();

    /**
     * The callback function to be called before adding data to the model. This
     * callback works only when the user adds data to the model through the admin
     * component. If you want a callback which works with all other additions made
     * to the model (without necessarily going through the admin component),
     * then you might want to consider going through the Model::preAddHook
     * function.
     * @var string
     * @see Model::preAddHook()
     */
    public $preAddCallback;

    /**
     * The callback function to be called after adding data to the model.
     * @var string
     */
    public $postAddCallback;

    /**
     * The callback function to be called before editing the data in the model
     * @var string
     */
    public $preEditCallback;

    /**
     * The callback function to be called after editing data in the model
     * @var string
     */
    public $postEditCallback;

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
    public $sections = array();
    
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
    
    
    public $rowOperation;
    public $rowTemplate;
    public $hasEditOperation = true;
    public $hasAddOperation = true;
    public $entity;

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
    	// Check for wrong page numbers
        if($pageNumber < 1)
        {
            throw new \Exception("Illegal page number in admin component. Page numbers cannot be less than 1");
        }
        
        //execute the page enxtension method
        if($this->consoleMode)
        {
            $pageExtensionMethodName = Ntentan::camelize(Ntentan::plural($this->entity),".","", true) . 'AdminPage';
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
        }
        

        $this->setupOperations();
        $this->operationsTemplate = $this->operationsTemplate == null ? 'operations.tpl.php': $this->operationsTemplate;

        $this->set("operations_template", $this->operationsTemplate);
        $this->set("row_template", $this->rowTemplate);
        $this->set("entity", \ucfirst(Ntentan::plural($this->entity)));
        $this->set("notifications", $this->notifications);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("item_operation_url", Ntentan::getUrl($this->consoleModeRoute. '/edit'));
        $this->set('route', $pageControllerRoute);
        $itemsPerPage = 10;
        $model = $this->getModel();
        
        $this->view->template = "page.tpl.php";
        $listFields = $this->listFields;
        $description = $model->describe();
        if(count($listFields) == null)
        {
            $listFields = $model->getFields();
            array_shift($listFields);
        }
        $listFields[] = "id";
        

        $data = $model->get(
            $itemsPerPage,
            array(
                "fields"            =>  $listFields,
                "offset"            =>  ($pageNumber-1) * $itemsPerPage,
                "sort"              =>  $model->getName() . ".id desc",
                "fetch_belongs_to"  =>  true
            )
        );

        $count = $model->get('count');
        $data = $data->getData();
        $this->set("data", $data);
        if(count($data) > 0)
        {
            $headers = array_keys($data[0]);
            array_pop($headers);
        }
        $this->set("headers", $headers);
        $numPages = ceil($count / $itemsPerPage);
        $pagingLinks = array();

        $this->set("operations", $this->operations);

        if(count($this->listFields) == 0)
        {
            $description = $model->describe();
            foreach($description["fields"] as $field)
            {
                if($field["primary_key"] === true)
                {
                    continue;
                }
                else if($field["foreign_key"] === true)
                {
                    $this->listFields[] = Ntentan::singular($field["model"]);
                }
                else
                {
                    $this->listFields[] = $field["name"];
                }
            }
        }

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
            $newSection = array(
                'route' => $section,
                'label' => \ucwords(str_replace('/', ' ', $section)),
                'model' => str_replace('/', '.', $section),
                'entity' => Ntentan::singular(end(explode('.', str_replace('/', '.', $section))))
            );
            $section = $newSection;
        }
        $this->sections[$section['route']] = $section;
    }

    private function showConsolePage($pageNumber)
    {
        $this->page($pageNumber);
    }

    public function console()
    {
        // Setup layouts, templates and stuff
        $this->view->layout = 'admin.tpl.php';
        $this->view->template = 'console.tpl.php';
        $this->set("app_name", Ntentan::$config['application']['name']);
        $this->set("stylesheet", Ntentan::getFilePath("lib/controllers/components/admin/assets/css/admin.css"));
        
        $profile = $this->authComponent->getProfile();
        $this->set('username', $profile['username']);
        
        $this->headingLevel = '3';

        //Setup the menus to be used in this administrator section
        $menuItems = array();
        foreach($this->sections as $section)
        {
            $item['label'] = $section['label'];
            $item['url'] = Ntentan::getUrl($this->controller->route . "/console/{$section['route']}");
            $menuItems[] = $item;
        }
        $this->set('sections_menu', $menuItems);

        $arguments = func_get_args();
        if(count($arguments) == 0)
        {

        }
        else
        {
            if(end($arguments) == "add")
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
                            $this->showConsolePage($index);
                            break;
                        default:
                            $extensionMethodName = Ntentan::camelize(Ntentan::plural($this->entity),".","", true) . 'Admin' . Ntentan::camelize($action);
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
                $this->showConsolePage(1);
            }
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
        $route = $this->consoleModeRoute;
        Ntentan::redirect(
            "$route?n=3&i=" . base64_encode($item)
        );
    }

    public function edit($id)
    {
        $this->view->template = "{$this->entity}_edit.tpl.php";
        $description = $this->getModel()->describe();
        $this->set("fields", $description["fields"]);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $item = $this->getModel()->getFirstWithId($id);
        $data = $item->toArray();
        foreach($data as $key => $value)
        {
            $data[$key] = Janitor::cleanHtml($value);
        }
        $this->set("data", $data);
        $this->set("entity", $this->entity);

        if(count($_POST) > 0)
        {
            $item->setData($_POST, true);
            $item->id = $id;
            if($item->update())
            {
                $route = $this->consoleModeRoute;
                Ntentan::redirect(
                    "$route?n=2&i=" . base64_encode($item)
                );
            }
            else
            {
                $this->set('errors', $item->invalidFields);
            }
        }

        if($this->consoleMode)
        {
            $editExtensionMethodName = Ntentan::camelize(Ntentan::plural($this->entity),".","", true) . 'AdminEdit';
            if(method_exists($this->controller, $editExtensionMethodName))
            {
                $editExtensionMethod = new ReflectionMethod($this->controller, $editExtensionMethodName);
                $editExtensionMethod->invoke($this->controller);
            }
        }
    }

    public function add()
    {
        $model = $this->getModel();
        $description = $model->describe();
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("fields", $description["fields"]);
        $this->set("entity", $this->entity);
        $this->view->template = "{$this->entity}_add.tpl.php";
        
        if($this->consoleMode)
        {
            $addExtensionMethodName = Ntentan::camelize(Ntentan::plural($this->entity),".","", true) . 'AdminAdd';
            if(method_exists($this->controller, $addExtensionMethodName))
            {
                $addExtensionMethod = new ReflectionMethod($this->controller, $addExtensionMethodName);
                $addExtensionMethod->invoke($this->controller);
            }
        }        

        if(count($_POST) > 0)
        {
            $this->executeCallbackMethod($this->preAddCallback);
            $model->setData($_POST);
            $id = $model->save();
            if($id > 0)
            {
                $route = $this->consoleModeRoute;

                if(!$this->executeCallbackMethod($this->postAddCallback, $id, $model))
                {
                    Ntentan::redirect(
                        "$route?n=1&i=" . base64_encode($model)
                    );
                }
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
