<?php
/**
 * @license MIT
 * @author  James Ekow Abaka Ainooson <jainooson@gmail.com>
 */
class ListView
{
    public $data;
    private $_columns;

    public function __construct($data, $columns = null)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function __set($parameter, $value)
    {
        switch($parameter)
        {
            case "columns":
                if($value == null)
                {
                    $fields = array_keys($this->data[0]);
                    array_shift($fields);
                    foreach($fields as $field)
                    {
                        $this->_columns[] = array("name"=>$field, "label"=>Ntentan::toSentence($field));
                    }
                }
                else
                {
                    $this->_columns = $value;
                }
                break;
        }
    }

    public function operationsRenderer($data, $column)
    {
        foreach($column["operations"] as $operation)
        {
            $return .= "<a href='{$operation["path"]}/$data'>{$operation["label"]}</a> ";
        }
        return $return;
    }

    public function textRenderer($data, $column)
    {
        return $data;
    }

    public function imageRenderer($data, $column)
    {
        if($data != "")
        {
            $width = $column["image_options"]["width"] == 0 ? 50 : $column["image_options"]["width"];
            $height = $column["image_options"]["height"] == 0 ? 50 : $column["image_options"]["height"];

            $imgSrc = ImageCache::thumbnail($data, $width, $height, true);
            return "<img src='/$imgSrc' width='$width' height='$height' />";
        }
    }

    public function __get($parameter)
    {
        switch($parameter)
        {
            case "columns":
                return $this->_columns;
        }
    }

    private function render($data, $column)
    {
        if(isset($column["renderer"]))
        {
            try
            {
                $methodName = "{$column["renderer"]}Renderer";
                $methodReflection = new ReflectionMethod($this, $methodName);
                $ret = $methodReflection->invokeArgs($this, array($data, $column));
            }
            catch(Exception $e)
            {
                var_dump($e->getMessage());
            }
        }
        else
        {
            $ret = $this->textRenderer($data, $column);
        }
        return $ret;
    }

    public function __toString()
    {
        ob_start();
        echo "<table><thead><tr>";
        foreach($this->_columns as $column)
        {
            echo "<td>{$column["label"]}</td>";
        }
        echo "</tr></thead><tbody>";
        $fill = false;
        foreach($this->data as $data)
        {
            echo "<tr " . ($fill ? 'class="even-stripe"' : "") . ">";
            foreach($this->_columns as $column)
            {
                echo "<td>".$this->render($data[$column["name"]], $column)."</td>";
            }
            $fill = !$fill;
            echo "</tr>";
        }
        echo "</tbody></table>";
        return ob_get_clean();
    }
}