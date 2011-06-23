<?php
/*
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\models;

use ntentan\Ntentan;
use ntentan\models\exceptions\ModelNotFoundException;
use ntentan\models\exceptions\MethodNotFoundException;
use ntentan\models\exceptions\FieldNotFoundException;
use ntentan\caching\Cache;
use \ArrayAccess;
use \Iterator;
use \ReflectionObject;
use \ReflectionMethod;

/**
 * The Model class
 */
class Model implements ArrayAccess, Iterator
{
	const RELATIONSHIP_BELONGS_TO = 'belongs_to';
	
	const RELATIONSHIP_HAS_MANY = 'has_many';
    /**
     * 
     * @var array
     */
    protected $data = array();

    /**
     * Previous data kept for validation purposes.
     * @var unknown_type
     */
    private $previousData;

    /**
     * The name of the current datastore
     * @var string
     */
    public $dataStore;

    /**
     * Field for checking the relationship between two different models
     * @var string
     */
    public $belongsTo = array();
    public $hasMany = array();
    public $mustBeUnique;
    public $belongsToModelInstances = array();
    private $route;
    private $name;
    public $invalidFields = array();
    private $iteratorPosition;
    public $defaultField;

    public function __construct()
    {
        if($this->belongsTo != null)
        {
            if(is_array($this->belongsTo))
            {
                foreach($this->belongsTo as $belongsTo)
                {
                    $this->belongsToModelInstances[] = Model::load(Model::getBelongsTo($belongsTo));
                }
            }
            else
            {
                $this->belongsToModelInstances[] = Model::load(Model::getBelongsTo($this->belongsTo));
            }
        }
        $modelInformation = new ReflectionObject($this);
        $modelName = end(explode("\\", $modelInformation->getName()));
        $this->name = strtolower(Ntentan::deCamelize($modelName));

        $this->iteratorPosition = 0;
        $skip = count(explode("/", Ntentan::$modulesPath));
        $this->route = implode(".",array_slice(explode("\\", $modelInformation->getName()), count(explode("/", Ntentan::$modulesPath)) + 1, -1));

        $dataStoreParams = Ntentan::getDefaultDataStore();
        $dataStoreClass = __NAMESPACE__ . "\\datastores\\" . Ntentan::camelize($dataStoreParams["datastore"]);
        if(class_exists($dataStoreClass))
        {
            $dataStore = new $dataStoreClass($dataStoreParams);
            $this->setDataStore($dataStore);
        }
        else
        {
            throw new exceptions\DataStoreException("Datastore {$dataStoreClass} doesn't exist.");
        }
    }

    public static function getBelongsTo($belongsTo)
    {
        return Ntentan::plural(is_array($belongsTo) ? $belongsTo[0] : $belongsTo);
    }

    public static function getClassName($className)
    {
        $classNameArray = explode('.', $className);
        $className = Ntentan::camelize(end($classNameArray));
        $fullClassName = "\\" . str_replace("/", "\\", Ntentan::$modulesPath) . "\\modules\\" . implode("\\", $classNameArray) . "\\$className";
        $modelClassFile = Ntentan::$modulesPath . '/modules/' . implode('/', $classNameArray) . "/$className.php" ;
        if(!file_exists($modelClassFile))
        {
            throw new ModelNotFoundException("Model class <b><code>$fullClassName</code></b> not found");
        }
        return $fullClassName;
    }

    public static function splitName($modelField)
    {
    	$modelArray = explode('.', $modelField);
    	$return['field'] = array_pop($modelArray);
    	$return['model'] = implode('.', $modelArray);
    	
    	return $return;
    }
    
    public static function extractModelName($modelField)
    {
    	$split = self::splitName($modelField);
    	return $split['model'];
    }

    /**
     * Loads a model.
     * @param string $model
     * @return Model
     */
    public static function load($modelRoute)
    {
        $className = Model::getClassName($modelRoute);
        return new $className();
    }

    public function setData($data, $overwrite = false)
    {
        if($overwrite === true)
        {
            if(count($this->data) > 0)
            {
                $this->previousData = $this->data;
            }
            else
            {
                $this->previousData = $data;
            }
            $this->data = $data;
        }
        else
        {
            if(is_array($data))
            {
                foreach($data as $field => $value)
                {
                    $this->previousData[$field] = $this->data[$field];
                    $this->data[$field] = $value;
                }
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }
    
    public function getRelationshipWith($modelType)
    {
    	foreach($this->hasMany as $related)
    	{
    		if($related == $modelType) return Model::RELATIONSHIP_HAS_MANY;
    	}
    	foreach($this->belongsTo as $related)
    	{
    		if($related == $modelType) return Model::RELATIONSHIP_BELONGS_TO;
    	}
    }

    public function setDataStore($dataStore)
    {
        $this->dataStore = $dataStore;
        $this->dataStore->setModel($this);
    }

    public function getName()
    {
        return $this->name;
    }
    
    public function getRoute()
    {
        return $this->route;
    }

    public function get($type = 'all', $params = null)
    {
        if($type == null)
        {
            return new ModelQuery($this);
        }
        else
        {
            $params["type"] = $type;
            $result = $this->dataStore->get($params);
            return $result;
        }
    }

    public function getFields()
    {
        $description = $this->describe();
        return array_keys($description["fields"]);
    }

    public function preSaveCallback()
    {

    }

    public function postSaveCallback($id)
    {

    }

    public function preUpdateCallback()
    {

    }

    public function postUpdateCallback()
    {

    }

    public function preDeleteCallback()
    {

    }

    public function postDeleteCallback()
    {

    }

    public function save()
    {
        if($this->validate(true))
        {
            $this->dataStore->begin();
            $this->preSaveCallback();
            $this->dataStore->setModel($this);
            $id = $this->dataStore->put();
            $this->id = $id;
            $this->postSaveCallback($id);
            $this->dataStore->end();
            return $id;
        }
        else
        {
            return false;
        }
    }

    public function update()
    {
        if($this->validate())
        {
            $this->preUpdateCallback();
            $this->dataStore->setModel($this);
            $this->dataStore->update();
            $this->postUpdateCallback();
            return true;
        }
        else
        {
            return false;
        }
    }

    public function delete()
    {
        $this->preDeleteCallback();
        $this->dataStore->setModel($this);
        $this->dataStore->delete();
        $this->postDeleteCallback();
    }

    public static function __callstatic($method, $arguments)
    {
        $class = get_called_class();
        $object = new $class();
        return $object->__call($method, $arguments);
    }

    public function __call($method, $arguments)
    {
        $executed = false;
        if(substr($method, 0, 7) == "getWith")
        {
            $field = Ntentan::deCamelize(substr($method, 7));
            $type = 'all';
            foreach($arguments as $argument)
            {
                $params["conditions"][$this->route . "." . $field] = $argument;
            }
            return $this->get($type, $params);
        }

        if(substr($method, 0, 12) == "getFirstWith")
        {
            $field = Ntentan::deCamelize(substr($method, 12));
            $type = 'first';
            $conditions = array();
            foreach($arguments as $argument)
            {
                if(is_array($argument))
                {
                    $params = $argument;
                    break;
                }
                else
                {
                    $conditions[$this->route . "." . $field] = $argument;
                }
            }
            $params["conditions"] = is_array($params['conditions']) ? array_merge($conditions, $params['conditions']) : $conditions;
            if(!isset($params["fetch_related"])) $params["fetch_related"] = true;
            return $this->get($type, $params);
        }

        if(substr($method, 0, 10) == "getAllWith")
        {
            $field = Ntentan::deCamelize(substr($method, 10));
            $conditions = array();
            foreach($arguments as $argument)
            {
                if(is_array($argument))
                {
                    $params = $argument;
                    break;
                }
                else
                {
                    $conditions[$this->route . "." . $field] = $argument;
                }
            }
            $params["conditions"] = $conditions;
            if(!isset($params["fetch_related"])) $params["fetch_related"] = true;
            $type = isset($params['limit']) ? $params['limit'] : 'all';
            return $this->get($type, $params);
        }

        if($method == 'getFirst')
        {
            return $this->get(isset($arguments[0]['limit']) ? $arguments[0]['limit'] : 'first', $arguments[0]);
        }

        if($method == "getAll")
        {
            return $this->get(isset($arguments[0]['limit']) ? $arguments[0]['limit'] : 'all', $arguments[0]);
        }

        if(substr($method, 0, 3) == "get")
        {
            $modelName = strtolower(substr($method,3));
            $modelMethod = new ReflectionMethod($model, "get");
            $foreignKey = $this->name . "_id";
            //$arguments[1]["conditions"] = array($this->name . "_id" => $this->data["id"]);

            $keys = array_keys($this->data);
            if($keys[0] == "0")
            {
                foreach($this->data as $key => $row)
                {
                    $arguments[0] = isset($arguments[0]) ? $arguments[0] : 'all' ;
                    $arguments[1]["conditions"] = array(Ntentan::singular($this->name) . "_id" => $row["id"]);

                    $this->data[$key][$model->name] = $modelMethod->invokeArgs($model, $arguments);
                }
            }
            else
            {

            }

            return $modelMethod->invokeArgs($model, $arguments);
        }
        throw new MethodNotFoundException($method);
    }

    public function __set($variable, $value)
    {
        $this->previousData[$variable] = $this->data[$variable];
        $this->data[$variable] = $value;
    }

    public function __get($variable)
    {
        if(isset($this->data[$variable]))
        {
            return $this->data[$variable];
        }
        else
        {
            //throw new FieldNotFoundException("Field [$variable] not found in Model");
        }
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
            $newModel->setData($this->data[$offset], true);
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

    public function count()
    {
        return count($this->data);
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current()
    {
        $newModel = clone $this;
        $newModel->setData($this->data[$this->iteratorPosition], true);
        return $newModel;
    }

    public function key()
    {
        return $this->iteratorPosition;
    }

    public function next()
    {
        $this->iteratorPosition++;
    }

    public function valid()
    {
        return isset($this->data[$this->iteratorPosition]);
    }

    public function describe()
    {
        if(!Cache::exists("model_" . $this->route))
        {
            $description = $this->dataStore->describe();
            if(is_array($this->mustBeUnique))
            {
                foreach($description["fields"] as $i => $field)
                {
                    $uniqueField = false;

                    foreach($this->mustBeUnique as $unique)
                    {
                        if(is_array($unique))
                        {
                            if(isset($unique['field']))
                            {
                                if($field["name"] == $unique["field"])
                                {
                                    $uniqueField = true;
                                    $uniqueMessage = $unique["message"];
                                }
                            }
                            else
                            {
                                throw new exceptions\DescriptionException("A mustBeUnique constraint specified as an array must always contain a field property");
                            }
                        }
                        else
                        {
                            if($field["name"] == $unique)
                            {
                                $uniqueField = true;
                                $uniqueMessage = null;
                            }
                        }
                    }

                    if($uniqueField)
                    {
                        $description["fields"][$i]["unique"] = true;
                        if($uniqueMessage != null)
                        {
                            $description["fields"][$i]["unique_violation_message"] = $uniqueMessage;
                        }
                    }
                }
            }

            if(is_array($this->belongsTo))
            {
                foreach($this->belongsTo as $belongsTo)
                {
                    $belongsToModel = is_array($belongsTo) ? $belongsTo[0] : $belongsTo;
                    $description["belongs_to"][] = $belongsToModel;
                    $alias = null;
                    if(is_array($belongsTo))
                    {
                        $fieldName = $belongsTo["as"];
                        $alias = $belongsTo["as"];
                    }
                    else
                    {
                        $alias = strtolower(
                            Ntentan::singular(
                                $this->getBelongsTo($belongsTo)
                            )
                        );
                        $fieldName = $alias . "_id";
                    }
                    foreach($description["fields"] as $i => $field)
                    {
                        if($field["name"] == $fieldName)
                        {
                            $description["fields"][$i]["model"] = Ntentan::plural($belongsToModel);
                            $description["fields"][$i]["foreign_key"] = true;
                            $description["fields"][$i]["field_name"] = $fieldName;
                            if($alias != '') $description["fields"][$i]["alias"] = $alias;
                        }
                    }
                }
            }
            else
            {
                if($this->belongsTo != null)
                {
                    $description["belongs_to"][] = $this->belongsTo;
                    $fieldName = strtolower(Ntentan::singular($this->belongsTo)) . "_id";
                    foreach($description["fields"] as $i => $field)
                    {
                        if($field["name"] == $fieldName)
                        {
                            $description["fields"][$i]["model"] = $this->belongsTo;
                            $description["fields"][$i]["foreign_key"] = true;
                        }
                    }
                }
            }
            Cache::add("model_" . $this->route, $description);
        }
        return Cache::get("model_" . $this->route);
    }

    public function __toString()
    {
        if(is_string($this->data))
        {
            return $this->data;
        }
        else if(is_array($this->data))
        {
            return json_encode($this->data, true);
        }
    }

    public function toArray()
    {
        $data = $this->getData();
        if(!is_array($data)) return null;
        $keys = array_keys($data);

        $returnData = array();

        if($keys[0] == '0')
        {
            foreach($data as $index => $row)
            {
                foreach($row as $key => $value)
                {
                    $returnData[$index][$key] = is_object($value) ? $value->toArray() : $value;
                }
            }
        }
        else
        {
            foreach($data as $key => $row)
            {
                if(is_object($data[$key]))
                {
                    $returnData[$key] = $data[$key]->toArray();
                }
                else
                {
                    $returnData[$key] = $row;
                }
            }
        }
        return $returnData;
    }

    public function validate($inserting = false)
    {
        $description = $this->describe();

        foreach($description["fields"] as $field)
        {
            if($field["primary_key"]) continue;

            // Validate Required
            if(($this->data[$field["name"]] === "" || $this->data[$field["name"]] === null) && $field["required"])
            {
                if(!($inserting && isset($field["default"])))
                {
                    $this->invalidFields[$field["name"]][] = "This field is required";
                }
            }

            // Validate unique
            if($field["unique"] === true && ($this->data[$field["name"]] != $this->previousData[$field["name"]]))
            {
                $value = $this->get('first', array("conditions"=>array($field["name"] => $this->data[$field["name"]])));
                if(count($value->getData()))
                {
                    $this->invalidFields[$field["name"]][] = isset($field["unique_violation_message"]) ?
                        $field["unique_violation_message"] :
                        "This field must be unique";
                }
            }
        }
        if(count($this->invalidFields) == 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
