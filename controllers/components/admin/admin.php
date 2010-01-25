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
        $this->viewTemplate = "list";
        $data = $this->model->get();
        $this->set("data", $data->getData());
    }

    protected function addItem()
    {
        $save = false;
        $this->viewTemplate = "add";
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
            $this->model->save();
        }
    }

    protected function editItem()
    {

    }

    protected function deleteItem()
    {
        
    }
}