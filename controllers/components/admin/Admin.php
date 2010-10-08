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
 * 
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
    public $preAddCallback;
    public $postAddCallback;
    public $preEditCallback;
    public $postEditCallback;
    public $preDeleteCallback;
    public $postDeleteCallback;
    public $prefix;
    public $consoleMode = false;
    public $sections = array();
    public $model;
    private $operations;
    private $site;

    public function __construct($prefix = null)
    {
        $this->prefix = $prefix;
        include "config/site.php";
        $this->site = $site;
    }

    public function addOperation($operation)
    {
        if(!isset($operation["controller"]))
        {
            $operation["controller"] = 
                $this->consoleMode ? 
                    $this->controller->path . "/console/" . $this->getModel()->getName() : 
                    $this->controller->path;
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
        $this->addOperation(
            array(
                "label"=> "Edit", 
                "operation" => "edit"
            )
        );

        $this->addOperation(
            array(
                "label"=> "Delete",
                "operation" => "delete",
                "confirm_message" => "Are you sure you want to delete <b>%item%</b>?"
            )
        );
    }

    public function page($pageNumber)
    {
        $this->setupOperations();
        $this->set("model", ucfirst($this->getModel()->getName()));
        $itemsPerPage = 5;
        $model = $this->getModel();
        $this->useTemplate("page.tpl.php");

        $data = $model->get(
        $itemsPerPage,
            array(
                "offset"=>($pageNumber-1) * $itemsPerPage,
                "sort" => "id desc"
            )
        );

        $count = $model->get('count');
        $this->set("data", $data->getData());
        $numPages = ceil($count / $itemsPerPage);
        $pagingLinks = array();

        $this->set("operations", $this->operations);

        if(count($this->listFields) == 0)
        {
            $description = $model->describe();
            foreach($description["fields"] as $field)
            {
                if($field["primary_key"] == true) continue;
                $this->listFields[] = $field["name"];
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
                    "link" => Ntentan::getUrl("{$this->prefix}/{$this->controller->path}/page/" . ($pageNumber - 1)),
                    "label" => "< Prev"
                );
            }

            for($i = 1; $i <= $numPages; $i++)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl("{$this->prefix}/{$this->controller->path}/page/$i"),
                    "label" => "$i"
                );
            }

            if($pageNumber < $numPages)
            {
                $pagingLinks[] = array(
                    "link" => Ntentan::getUrl("{$this->prefix}/{$this->controller->path}/page/" . ($pageNumber + 1)),
                    "label" => "Next >"
                );
            }
            $this->set("pages", $pagingLinks);
        }
    }
    
    public function addSection($section)
    {
        $this->sections[$section["group"]][] = $section;
    }

    public function console()
    {
        // Setup layouts, templates and stuff
        $this->useLayout("console.tpl.php");
        $this->useTemplate("run.tpl.php");
        $this->set("site_name", $this->site["name"]);
        $this->view->layout->addStyleSheet(
            Ntentan::getFilePath("stylesheets/grid.css")
        );
        $this->view->layout->addStyleSheet(
            Ntentan::getFilePath(
                "controllers/components/admin/stylesheets/admin.css"
            )
        );
        $this->view->layout->addStyleSheet(
            Ntentan::getFilePath(
                "stylesheets/ntentan.css"
            )
        );
        
        //Setup the menus to be used in this administrator section
        
        $this->addBlock("menu", "default_menu");
        foreach($this->sections as $section)
        {
            $item = array();
            if(is_array($section))
            {
                
            }
            else if (is_string($section))
            {
                $item["label"] = Ntentan::toSentence($section);
                $item["path"] = Ntentan::getUrl($this->controller->path . "/console/$section");
                $this->defaultMenuBlock->addItem($item);
            }
        }
        
        $arguments = func_get_args();
        if(count($arguments) == 0)
        {
            $this->view->layout->title = $this->site["name"] . " Administrator Console";
        }
        else
        { 
            if(end($arguments) == "add")
            {
                array_pop($arguments);
                $this->model = Model::load(implode(".", $arguments));
                $this->addBlock("menu", "console_menu");
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
                $this->model = Model::load(implode(".", $arguments));
                $this->view->layout->title = ucfirst($this->model->getName()) . " | " . $this->site["name"] . " Administrator Console";
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
        $this->set("positive_path", Ntentan::getUrl("{$this->prefix}/{$this->controller->path}/$operation/$id"));
        $this->set("negative_path", Ntentan::getUrl("{$this->prefix}/{$this->controller->path}"));
    }

    public function delete($id)
    {
        $this->view = false;
        $item = $this->getModel()->getFirstWithId($id);
        $item->delete();
        Ntentan::redirect(
            "{$this->prefix}/{$this->controller->path}?n=" . 
            urlencode(
                "Successfully deleted " . 
                Ntentan::singular($this->getModel()->getName()) .
                " <b>" . $item . "</b>"
            )
        );
    }

    public function edit($id)
    {
        $this->useTemplate("edit.tpl.php");
        $description = $this->getModel()->describe();
        $this->set("fields", $description["fields"]);
        $data = $this->getModel()->getFirstWithId($id);
        $this->set("data", $data->getData());
        if(count($_POST) > 0)
        {
            $data->setData($_POST);
            if($data->update())
            {
                if($this->consoleMode)
                {
                    $path = "{$this->controller->path}/console/{$this->getModel()->getName()}";
                }
                else
                {
                    $path = "{$this->prefix}/{$this->controller->path}";
                }                
                Ntentan::redirect(
                    "$path?n=2&i=" . base64_encode($data)
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
        $this->set("fields", $description["fields"]);
        $this->set("model", ucfirst(Ntentan::singular($this->getModel()->getName())));

        if(count($_POST) > 0)
        {
            $this->executeCallbackMethod($this->preAddCallback);
            $model->setData($_POST);
            $id = $model->save();
            if($id > 0)
            {
                if($this->consoleMode)
                {
                    $path = "{$this->controller->path}/console/{$model->getName()}";
                }
                else
                {
                    $path = "{$this->prefix}/{$this->controller->path}";
                }
                
                if(!$this->executeCallbackMethod($this->postAddCallback, $id, $model))
                {
                    Ntentan::redirect(
                        "$path?n=" . urlencode("Successfully added new ".$this->getModel()->getName()." <b>". (string)$model. "</b>")
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
