<?php
class admin extends AbstractComponent
{
    /**
     * 
     * @var Model
     */
    protected $model;
    private $basePath;
    private $modelName;
    private $utilClassName;

    public function manage()
    {
        $arguments = func_get_args();
        end($arguments);
        $last = current($arguments);

        if(!is_numeric($last) && $last != "add")
        {
            $this->model = Model::load(implode(".", $arguments));
            foreach($arguments as $argument)
            {
                $this->modelName .= ucfirst($argument) . " ";
                $this->utilClassName .= ucfirst($argument);
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
            }
            $this->addItem();
        }

        $this->set("model_name", $this->modelName);
        $this->set("util_class_name", $this->utilClassName);
        $this->set("controller_path", $this->path);
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
                array("path" => Ntentan::getUrl(Ntentan::$route . "export"), "label" => "Export"),
                array("path" => Ntentan::getUrl(Ntentan::$route . "template"), "label" => "Template"),
                array("path" => Ntentan::getUrl(Ntentan::$route . "import"), "label" => "Import"),
            )
        );
    }

    protected function addItem()
    {
        $save = false;
        $this->view->template = Ntentan::getFilePath("controllers/components/admin/add.tpl.php");
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
                $data[$field["name"]] = $_REQUEST[$field["name"]];
            }

            $this->callControllerMethod("preAdd", $data);
            $this->callControllerMethod("preAdd{$this->utilClassName}", $data);

            $this->model->setData($data);
            $validate = $this->model->validate();
            
            if($validate === true)
            {
                $this->model->save();

                $this->callControllerMethod("postAdd");
                $this->callControllerMethod("postAdd{$this->utilClassName}");

                Ntentan::redirect($this->basePath);
            }
            else
            {
                $this->set("form_errors", $validate);
            }
        }
    }

    protected function editItem()
    {

    }

    protected function deleteItem()
    {
        
    }
}