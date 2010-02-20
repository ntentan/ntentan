<?php
/**
 * The Model class 
 */
class Model implements ArrayAccess
{
    /**
     * 
     * @var array
     */
    protected $data;

    /**
     * An instance of the datastore.
     * @var DataStore
     */
    private $_dataStoreInstance;

    /**
     * The name of the current datastore 
     * @var string
     */
    protected $dataStore;

    /**
     * Field for checking the relationship between two different models
     * @var string
     */
    public $belongsTo;
    public $belongsToModelInstances;

    public function __construct()
    {
        if($this->belongsTo != null)
        {
            $this->belongsToModelInstances = Model::load($this->belongsTo);
        }
    }

    /**
     * Loads a model.
     * @param string $model
     * @return Model
     */
    public static function load($model)
    {
        $pathComponents = explode(".", $model);
        $modelClass = ucfirst($pathComponents[0]) . "Model";
        $modelFile = Ntentan::$packagesPath . implode("/", $pathComponents) . "/$modelClass.php";

        if(!file_exists($modelFile))
        {
            throw new ModelNotFoundException("Cannot find [$modelFile]");
        }

        require_once
        (
            Ntentan::$packagesPath
            . implode("/", $pathComponents)
            . "/$modelClass.php"
        );

        $model = new $modelClass();

        if($model->datastore == null)
        {
            $dataStoreParams = Ntentan::getDefaultDataStore();
            $dataStoreClass = ucfirst($dataStoreParams["datastore"]) . "DataStore";
            $dataStore = new $dataStoreClass($dataStoreParams);
            $model->setDataStore($dataStore);
        }
        return $model;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setDataStore($dataStore)
    {
        $this->_dataStoreInstance = $dataStore;
        $this->_dataStoreInstance->setModel($this);
    }

    public function get($type = 'all', $params = null)
    {
        $params["type"] = $type;
        $result = $this->_dataStoreInstance->get($params);
        return $result;
    }

    public function save()
    {
        $this->_dataStoreInstance->put();
    }

    public function update()
    {
        $this->_dataStoreInstance->update();
    }

    public function delete()
    {
        $this->_dataStoreInstance->setModel($this);
        $this->_dataStoreInstance->delete();
    }

    public function __call($method, $arguments)
    {
        if(substr($method, 0, 7) == "getWith")
        {
            $field = strtolower(substr($method, 7));
            $type = 'all';
            foreach($arguments as $argument)
            {
                $params["conditions"][$field] = $argument;
            }
        }

        if(substr($method, 0, 12) == "getFirstWith")
        {
            $field = strtolower(substr($method, 12));
            $type = 'first';
            foreach($arguments as $argument)
            {
                $params["conditions"][$field] = $argument;
            }
        }

        return $this->get($type, $params);
    }

    public function __set($variable, $value)
    {
        $this->data[$variable] = $value;
    }

    public function __get($variable)
    {
        return $this->data[$variable];
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if(is_array($this->data[$offset]))
        {
            $newModel = clone $this;
            $newModel->setData($this->data[$offset]);
            $ret = $newModel;
        }
        else
        {
            $ret = $this->data[$offset];
        }
        return $ret;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function length()
    {
        return count($this->data);
    }

    public function describe()
    {
        $description = $this->_dataStoreInstance->describe();
        if($this->belongsTo != "")
        {
            $description["belongs_to"] = $this->belongsTo;
            $fieldName = strtolower(Ntentan::singular($this->belongsTo)) . "_id";
            foreach($description["fields"] as $i => $field)
            {
                if($field["name"] == $fieldName)
                {
                    $description["fields"][$i]["model"] = $this->belongsTo;
                    $description["fields"][$i]["foreing_key"] = true;
                }
            }
        }
        return $description;
    }

    public function __toString()
    {
        if(is_string($this->data))
        {
            return $this->data;
        }
        else if(is_array($this->data))
        {
            return print_r($this->data, true);
        }
    }

    public function validate()
    {
        $description = $this->describe();
        $errors = array();
        foreach($description["fields"] as $field)
        {
            if($field["primary_key"]) continue;
            // Validate Required
            if($this->data[$field["name"]] == "" && $field["required"])
            {
                $errors[$field["name"]][] = "This field is required";
            }
        }
        if(count($errors) == 0) return true; else return $errors;
    }
}