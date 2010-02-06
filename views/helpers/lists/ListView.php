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
                break;
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
                echo "<td>{$data[$column["name"]]}</td>";
            }
            $fill = !$fill;
            echo "</tr>";
        }
        echo "</tbody></table>";
        return ob_get_clean();
    }
}