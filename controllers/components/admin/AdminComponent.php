<?php
class AdminComponent extends Component
{
    /**
     * 
     * @var Model
     */
    //protected $adminModel;
    private $basePath;
    private $modelName;
    private $utilClassName;
    private $baseClassName;
    public $tempData;

    public function manage()
    {
        $arguments = func_get_args();
        end($arguments);
        $last = current($arguments);
        $lastButOne = prev($arguments);

        if(!is_numeric($last) && $last != "add")
        {
            $this->model = Model::load(implode(".", $arguments));
            foreach($arguments as $argument)
            {
                $this->modelName .= ucfirst($argument) . " ";
                $this->utilClassName .= ucfirst($argument);
                $this->baseClassName .= strtolower($argument) . "_";
            }
            $this->listItems();
        }
        else if($last == "add")
        {
            array_pop($arguments);
            $this->model = Model::load(implode(".", $arguments));
            $this->basePath = $this->path . "/manage/" . implode("/", $arguments) . "/";
            foreach($arguments as $argument)
            {
                $this->modelName .= ucfirst($argument) . " ";
                $this->utilClassName .= ucfirst($argument);
                $this->baseClassName .= strtolower($argument) . "_";
            }
            $this->addItem();
        }
        else if(is_numeric($last) && $lastButOne == "edit")
        {
            $key = $last;
            array_pop($arguments);
            array_pop($arguments);
            $this->model = Model::load(implode(".", $arguments));
            $this->basePath = $this->path . "/manage/" . implode("/", $arguments) . "/";
            foreach($arguments as $argument)
            {
                $this->modelName .= ucfirst($argument) . " ";
                $this->utilClassName .= ucfirst($argument);
                $this->baseClassName .= strtolower($argument) . "_";
            }
            $this->editItem($key);
        }
        else if(is_numeric($last) && $lastButOne = "delete")
        {
            $key = $last;
            array_pop($arguments);
            array_pop($arguments);
            $this->model = Model::load(implode(".", $arguments));
            $this->basePath = $this->path . "/manage/" . implode("/", $arguments) . "/";
            $this->deleteItem($key);
        }

        $this->set("model_name", $this->modelName);
        $this->set("util_class_name", $this->utilClassName);
        $this->set("base_class_name", $this->baseClassName);
        $this->set("controller_path", $this->path);
        $this->set("model_description", $this->model->describe());
    }

    protected function listItems()
    {
        $this->view->template = Ntentan::getFilePath("controllers/components/admin/list.tpl.php");
        $data = $this->model->get();
        $count = $this->model->get('count');
        $this->set("list_data", $data->getData());
        $this->set("num_list_data", (int)(string)$count);
        $this->set
        (
            "operations",
            array(
                array("path" => Ntentan::getUrl(Ntentan::$route . "add"), "label" => "Add"),
                /*array("path" => Ntentan::getUrl(Ntentan::$route . "export"), "label" => "Export"),
                array("path" => Ntentan::getUrl(Ntentan::$route . "template"), "label" => "Template"),
                array("path" => Ntentan::getUrl(Ntentan::$route . "import"), "label" => "Import"),*/
            )
        );
        $this->set(
            "side_operations",
            array(
                array("path" => Ntentan::getUrl(Ntentan::$route . "edit"), "label" => "Edit"),
                array("path" => Ntentan::getUrl(Ntentan::$route . "delete"), "label" => "Delete"),
            )
        );
    }

    protected function addItem()
    {
        $this->view->template = Ntentan::getFilePath("controllers/components/admin/add.tpl.php");
        $save = false;
        $description = $this->model->describe();
        $this->set("fields", $description["fields"]);
        $this->set("name", $description["name"]);

        foreach($description["fields"] as $field)
        {
            if(isset($_REQUEST[$field["name"]]))
            {
                $save = true;
            }
        }

        if($save)
        {
            foreach($description["fields"] as $field)
            {
                if($field["primary_key"]) continue;
                $this->tempData[$field["name"]] = $_REQUEST[$field["name"]];
            }

            $this->callControllerMethod("preValidate");
            $this->callControllerMethod("preValidate{$this->utilClassName}");

            $this->model->setData($this->tempData);
            $validate = $this->model->validate();
            
            if($validate === true)
            {
                try
                {
                    $this->callControllerMethod("postValidate");
                    $this->callControllerMethod("postValidate{$this->utilClassName}");

                    $this->callControllerMethod("preAdd");
                    $this->callControllerMethod("preAdd{$this->utilClassName}");

                    $this->model->save();

                    $this->callControllerMethod("postAdd");
                    $this->callControllerMethod("postAdd{$this->utilClassName}");
                }
                catch (ControllerMethodNotFoundException $exception)
                {
                    
                }

                Ntentan::redirect($this->basePath);
            }
            else
            {
                $this->set("form_errors", $validate);
            }
        }
    }

    protected function editItem($key)
    {
        $this->view->template = Ntentan::getFilePath("controllers/components/admin/edit.tpl.php");
        $description = $this->model->describe();
        $this->set("fields", $description["fields"]);
        $this->set("name", $description["name"]);
        $data = $this->model->getFirstWithId($key);
        $this->set("form_data", $data->getData());

        foreach($description["fields"] as $field)
        {
            if(isset($_REQUEST[$field["name"]]))
            {
                $save = true;
            }
        }

        if($save)
        {
            foreach($description["fields"] as $field)
            {
                if($field["primary_key"]) continue;
                $this->tempData[$field["name"]] = $_REQUEST[$field["name"]];
            }
            $this->tempData["id"] = $key;

            $this->callControllerMethod("preValidate");
            $this->callControllerMethod("preValidate{$this->utilClassName}");

            $this->model->getWithId($key);
            $this->model->setData($this->tempData);
            $validate = $this->model->validate();

            if($validate === true)
            {
                try
                {
                    $this->callControllerMethod("postValidate");
                    $this->callControllerMethod("postValidate{$this->utilClassName}");

                    $this->callControllerMethod("preUpdate");
                    $this->callControllerMethod("preUpdate{$this->utilClassName}");

                    $this->model->update();

                    $this->callControllerMethod("postUpdate");
                    $this->callControllerMethod("postUpdate{$this->utilClassName}");
                }
                catch (ControllerMethodNotFoundException $exception)
                {

                }

                Ntentan::redirect($this->basePath);
            }
            else
            {
                $this->set("form_errors", $validate);
            }
        }
    }

    protected function deleteItem($key)
    {
        if($_REQUEST["confirm"] == "yes")
        {
            $item = $this->model->getWithId($key);
            $item->delete();
            Ntentan::redirect($this->basePath);
        }
        else
        {
            $this->view->template = Ntentan::getFilePath("controllers/components/admin/delete.tpl.php");
        }
    }
}