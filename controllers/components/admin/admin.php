<?php
class admin extends AbstractComponent
{
    /**
     *
     * @var Model
     */
    protected $model;

    public function manage()
    {
        $arguments = func_get_args();
        end($arguments);
        $last = current($arguments);
        if(!is_numeric($last) && $last != "add")
        {
            $this->model = Model::load(implode(".", $arguments));
            $this->listItems();
        }
        else if($last == "add")
        {
            array_pop($arguments);
            $this->model = Model::load(implode(".", $arguments));
            $this->addItem();
        }
    }

    protected function listItems()
    {
        $this->view->template = "list";
        $data = $this->model->get();
        $count = $this->model->get('count');
        $this->set("list_data", $data->getData());
        $this->set("num_list_data", (int)(string)$count);
        $this->set
        (
            "operations",
            array(
                array("path" => "add", "label" => "Add"),
                array("path" => "export", "label" => "Export"),
                array("path" => "template", "label" => "Template"),
                array("path" => "import", "label" => "Import"),
            )
        );
    }

    protected function addItem()
    {
        $save = false;
        $this->view->template = "add";
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
                $this->model[$field["name"]] = $_REQUEST[$field["name"]];
            }

            $validate = $this->model->validate();
            
            if($validate === true)
            {
                $this->model->save();
                Ntentan::redirect(Ntentan::$route);
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