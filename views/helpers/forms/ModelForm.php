<?php

class ModelForm extends Form
{
    public function __construct($fields, $id = "form")
    {
        $this->setId($id);
        foreach($fields as $field)
        {
            if($field["primary_key"]) continue;

            $element = FormsHelper::getFieldElement($field);

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