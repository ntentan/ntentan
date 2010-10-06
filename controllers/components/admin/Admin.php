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
 * within the attached controllers package or namespace.
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
    public $extraOperations = array();
    public $preAddCallback;
    public $postAddCallback;
    public $preEditCallback;
    public $postEditCallback;
    public $preDeleteCallback;
    public $postDeleteCallback;
    public $prefix;
    public $managerMode = false;
    private $operations;
    
    public function __construct($prefix = "admin")
    {
        $this->prefix = $prefix;
    }
    
    public function init()
    {
        $this->addOperation(
            array(
                "label"=> "Edit", 
                "controller" => $this->controller->path,
                "operation" => "edit"
            )        
        );
        
        $this->addOperation(
            array(
                "label"=> "Delete", 
                "controller" => $this->controller->path,
                "operation" => "delete",
                "confirm_message" => "Are you sure you want to delete <b>%item%</b>?"
            )
        );
    }

    public function addOperation($operation)
    {
        if(!isset($operation["controller"]))
        {
            $operation["controller"] = $this->controller->path;
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

    public function page($pageNumber)
    {
        $itemsPerPage = 5;
        $model = $this->controller->model;
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
    
    public function manage()
    {
        $this->useLayout("manage.tpl.php");
        $this->useTemplate("run.tpl.php");
    }

    public function run()
    {
        if($this->managerMode)
        {
            $this->manage();
        }
        else
        {
            $this->page(1);
        }
    }

    public function confirm($operation, $id)
    {
        $this->useTemplate("confirm.tpl.php");
        $item = $this->controller->model->getFirstWithId($id);
        $this->set("item", (string)$item);
        $this->set("message", $this->operations[$operation]["confirm_message"]);
        $this->set("positive_path", Ntentan::getUrl("{$this->prefix}/{$this->controller->path}/$operation/$id"));
        $this->set("negative_path", Ntentan::getUrl("{$this->prefix}/{$this->controller->path}"));
    }

    public function delete($id)
    {
        $this->view = false;
        $item = $this->controller->model->getFirstWithId($id);
        $item->delete();
        Ntentan::redirect(
            "{$this->prefix}/{$this->controller->path}?n=" . 
            urlencode(
                "Successfully deleted " . 
                Ntentan::singular($this->controller->model->getName()) . 
                " <b>" . $item . "</b>"
            )
        );
    }

    public function edit($id)
    {
        $this->useTemplate("edit.tpl.php");
        
        $description = $this->controller->model->describe();
        $this->set("fields", $description["fields"]);
        
        $data = $this->controller->model->getFirstWithId($id);
        $this->set("data", $data->getData());
        
        if(count($_POST) > 0)
        {
            $data->setData($_POST);
            if($data->update())
            {
                Ntentan::redirect(
                    "{$this->prefix}/{$this->controller->path}?n=" . 
                    urlencode("Successfully updated " . Ntentan::singular($this->controller->model->name) . " <b>" . $data ."</b>")
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
        $model = $this->controller->model;
        $description = $model->describe();
        $this->set("fields", $description["fields"]);
        $this->set("model", ucfirst(Ntentan::singular($this->controller->model->getName())));
        
        if(count($_POST) > 0)
        {
            $this->executeCallbackMethod($this->preAddCallback);
            $model->setData($_POST);
            $id = $model->save();
            if($id > 0)
            {
                if(!$this->executeCallbackMethod($this->postAddCallback, $id, $model))
                {
                    Ntentan::redirect(
                        "{$this->prefix}/$this->controller->path?n=" . 
                        urlencode("Successfully added new ".$this->controller->model->getName()." <b>". (string)$model. "</b>")
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
}
