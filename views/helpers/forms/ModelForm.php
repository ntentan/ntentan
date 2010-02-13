<?php

class ModelForm extends Form
{
    public function __construct($fields, $id)
    {
        $this->setId($id);
        foreach($fields as $field)
        {
            if($field["primary_key"]) continue;
            switch($field["type"])
            {
                case "integer":
                    $element = new TextField(uc_words(str_replace("_", " ", $field["name"])));
                    $element->setAsNumeric();

                case "string":
                    if($field["lenght"] == 0)
                    {
                        $element = new TextArea(ucwords(str_replace("_", " ", $field["name"])));
                    }
                    else
                    {
                        $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                    }
            }

            if($element!=null)
            {
                $element->name = $field["name"];
                $element->required = $field["required"];
                $this->add($element);
            }
            $element = null;
        }
    }

    public function setFieldAs()
    {
        $parameters = func_get_args();
        
    }
}