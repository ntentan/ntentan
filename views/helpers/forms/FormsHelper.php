<?php

class FormsHelper extends Helper
{
    public static function getFieldElement($field)
    {
        switch($field["type"])
        {
            case "double":
                $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                $element->setAsNumeric();
                break;

            case "integer":
                if($field["foreing_key"]===true)
                {
                    $element = new ModelField(ucwords(str_replace("_", " ", substr($field["name"], 0, strlen($field["name"])-3))), $field["model"]);
                    $element->name = $field["name"];
                }
                else
                {
                    $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                    $element->setAsNumeric();
                }
                break;

            case "string":
                if($field["lenght"] == 0)
                {
                    $element = new TextArea(ucwords(str_replace("_", " ", $field["name"])));
                }
                else
                {
                    $element = new TextField(ucwords(str_replace("_", " ", $field["name"])));
                }
                break;
        }
        return $element;
    }

    public function getField($field)
    {
        return FormsHelper::getFieldElement($field);
    }

}