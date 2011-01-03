<?php
/**
 * The file for the Administration Component
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
 * @package    ntentan.contorllers.components.admin
 * @author     James Ekow Abaka Ainooson <jainooson@gmail.com>
 * @copyright  2010 James Ekow Abaka Ainooson
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */

namespace ntentan\controllers\components\admin;

use ntentan\Ntentan;
use ntentan\controllers\components\Component;
use ntentan\models\Model;

/**
 * Admin component provides an interface through which data in a model could be
 * manipulated. Adding this component to any controller automatically provides
 * the interface for adding, editing and deleting items in the model class found
 * within the attached controllers package or namespace. Apart from the manipulation
 * interface, the admin component also provides a full blown admin console site.
 * 
 * @author James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class Admin extends Component
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
     *
     * @var string
     */
    public $preDeleteCallback;
    public $postDeleteCallback;
    public $prefix;
    public $consoleMode = false;
    public $sections = array();
    public $model;
    public $headings = true;
    public $headingLevel = '2';
    public $notifications = true;
    public $showTemplate = true;
    private $operations = array();
    public $itemOperationUrl;
    public $operationsTemplate;
    public $rowOperation;
    public $rowTemplate;
    private $app;
    public $hasEditOperation = true;
    public $hasAddOperation = true;

    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
        include "config/app.php";
        $this->app = $app_name;
    }

    public function addOperation($operation)
    {
        if(!isset($operation["controller"]))
        {
            $operation["controller"] = 
                $this->consoleMode ? 
                    $this->controller->route . "/console/" . $this->getModel()->getName() : 
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
    
    private function getCurrentRoute()
    {
        if($this->consoleMode)
        {
            return "{$this->controller->route}/console/{$this->getModel()->getName()}";
        }
        else
        {
            return "{$this->prefix}/{$this->controller->route}";
        }
    }

    private function setupOperations()
    {
        if($this->hasAddOperation)
        {
            $this->addOperation(
                array(
                    "label"=> "Edit", 
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
        $this->setupOperations();
        $this->operationsTemplate = 
            $this->operationsTemplate == null ? 
                Ntentan::getFilePath(
                    'lib/controllers/components/admin/templates/operations.tpl.php'
                ):
                $this->operationsTemplate;

        $this->rowTemplate =
            $this->rowTemplate == null ?
                Ntentan::getFilePath(
                    'lib/controllers/components/admin/templates/row.tpl.php'
                ):
                $this->rowTemplate;

        $this->set("operations_template", $this->operationsTemplate);
        $this->set("row_template", $this->rowTemplate);
        $this->set("model", ucfirst($this->getModel()->getName()));
        $this->set("notifications", $this->notifications);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("item_operation_url", $this->itemOperationUrl);
        $itemsPerPage = 10;
        $model = $this->getModel();
        $table = $model->getDataStore(true)->table;
        if($this->showTemplate) $this->useTemplate("page.tpl.php");
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
            if($pageNumber > 1)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl(
                        "{$this->prefix}/{$this->controller->route}/page/" . ($pageNumber - 1)
                    ),
                    "label" => "< Prev"
                );
            }

            for($i = 1; $i <= $numPages; $i++)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl(
                        "{$this->prefix}/{$this->controller->route}/page/$i"
                    ),
                    "label" => "$i"
                );
            }

            if($pageNumber < $numPages)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl(
                        "{$this->prefix}/{$this->controller->route}/page/" . ($pageNumber + 1)
                    ),
                    "label" => "Next >"
                );
            }
            $this->set("pages", $pagingLinks);
        }
    }
    
    public function addSection($section)
    {
        $this->sections[] = $section;
    }

    public function console()
    {
        // Setup layouts, templates and stuff
        $this->useLayout("console.tpl.php");
        $this->useTemplate("run.tpl.php");
        $this->set("app_name", $this->app["name"]);
        $this->view->layout->addStyleSheet(
            Ntentan::getFilePath("css/grid.css")
        );
        $this->view->layout->addStyleSheet(
            array(
                Ntentan::getFilePath(
                    "lib/controllers/components/admin/css/admin.css"
                ),
                Ntentan::getFilePath(
                    "css/fx.css"
                ),
                Ntentan::getFilePath(
                    "lib/views/helpers/forms/css/forms.css"
                )
            )
        );
        
        //Setup the menus to be used in this administrator section
        $this->addWidget("menu", "default_menu");
        foreach($this->sections as $section)
        {
            $item = array();
            if(is_array($section))
            {
                
            }
            else if (is_string($section))
            {
                $item["label"] = Ntentan::toSentence($section);
                $item["url"] = Ntentan::getUrl($this->controller->route . "/console/$section");
                $this->defaultMenuWidget->addItem($item);
            }
        }
        
        $arguments = func_get_args();
        if(count($arguments) == 0)
        {
            $this->view->layout->title = $this->app["name"] . " Administrator Console";
        }
        else
        { 
            if(end($arguments) == "add")
            {
                array_pop($arguments);
                $this->model = Model::load(implode(".", $arguments));
                $this->add();
            }
            else if(is_numeric(end($arguments)))
            {
                $index = array_pop($arguments);
                $action = array_pop($arguments);
                if(end($arguments) == "confirm")
                {
                    array_pop($arguments);
                    $this->model = Model::load(implode(".", $arguments));
                    $this->confirm($action, $index);
                }
                else
                {
                    switch($action)
                    {
                        case "edit":
                            $this->model = Model::load(implode(".", $arguments));
                            $this->edit($index);
                            break;
                        case "delete":
                            $this->model = Model::load(implode(".", $arguments));
                            $this->delete($index);
                            break;
                    }
                }
            }
            else
            {
                $this->addWidget("menu", "item_actions_menu");
                $this->model = Model::load(implode(".", $arguments));
                $this->itemActionsMenuWidget->addItem(
                    array(
                        "label" => "Add new " . strtolower(Ntentan::singular($this->model->getName())),
                        "url"   =>  Ntentan::getUrl($this->getCurrentRoute() . "/add")
                    )
                );
                $this->view->layout->title = ucfirst($this->model->getName()) . " | " . $this->app["name"] . " Administrator Console";
                $this->page(1);
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
        $this->useTemplate("confirm.tpl.php");
        $item = $this->getModel()->getFirstWithId($id);
        $this->set("item", (string)$item);
        $this->set("message", $this->operations[$operation]["confirm_message"]);
        $this->set("heading_level", $this->headingLevel);
        $route = $this->getCurrentRoute();
        $this->set("positive_route", Ntentan::getUrl("$route/$operation/$id"));
        $this->set("negative_route", Ntentan::getUrl($route));
    }

    public function delete($id)
    {
        $this->view = false;
        $item = $this->getModel()->getFirstWithId($id);
        $item->delete();
        $route = $this->getCurrentRoute();
        Ntentan::redirect(
            "$route?n=3&i=" . base64_encode($item)
        );
    }

    public function edit($id)
    {
        $this->useTemplate("edit.tpl.php");
        $description = $this->getModel()->describe();
        $this->set("fields", $description["fields"]);
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $item = $this->getModel()->getFirstWithId($id);
        $this->set("data", $item->getData());
        $this->set("item", ucfirst(Ntentan::singular($this->getModel()->getName())));
        
        if(count($_POST) > 0)
        {
            $item->setData($_POST);
            if($item->update())
            {
                $route = $this->getCurrentRoute();
                Ntentan::redirect(
                    "$route?n=2&i=" . base64_encode($item)
                );
            }
            else
            {
                $this->set('errors', $user->invalidFields);
            }
        }
    }

    public function add()
    {
        $this->useTemplate("add.tpl.php");
        $model = $this->getModel();
        $description = $model->describe();
        $this->set("heading_level", $this->headingLevel);
        $this->set("headings", $this->headings);
        $this->set("fields", $description["fields"]);
        $this->set("model", ucfirst(Ntentan::singular($this->getModel()->getName())));

        if(count($_POST) > 0)
        {
            $this->executeCallbackMethod($this->preAddCallback);
            $model->setData($_POST);
            $id = $model->save();
            if($id > 0)
            {
                $route = $this->getCurrentRoute();
                
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
}
