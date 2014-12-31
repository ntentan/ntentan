<?php

/**
 * Source file for the model class
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category ORM
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
 */

namespace ntentan\models;

use ntentan\Ntentan;
use ntentan\models\exceptions\ModelNotFoundException;
use ntentan\exceptions\MethodNotFoundException;
use ntentan\caching\Cache;
use \ArrayAccess;
use \Iterator;
use \ReflectionObject;

/**
 * The Model class
 * 
 * @todo Allow for a new kind of not too strict relationship: may_belong_to
 */
class Model implements ArrayAccess, Iterator
{

    const RELATIONSHIP_BELONGS_TO = 'belongs_to';
    const RELATIONSHIP_HAS_MANY = 'has_many';
    const ON_COMMIT_SAVE = 'save';
    const ON_COMMIT_UPDATE = 'update';

    /**
     * @todo try to prefix this with an underscore to prevent clashes with other assignments
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

    /**
     * Field for checking the relationship between two different models
     * @var type 
     */
    public $hasMany = array();

    /**
     * An array that specifies which fields must be unique.
     * @var array 
     */
    public $mustBeUnique;

    /**
     * An array of all instances of the belongs to models
     * @var array
     */
    public $belongsToModelInstances = array();

    /**
     * The route or name of this model
     * @var string
     */
    private $route;
    private $name;
    public $invalidFields = array();
    private $iteratorPosition;
    public $defaultField;
    protected $uniqueViolationMessages = array();
    protected $requiredViolationMessages = array();
    public $count = 0;
    public $hasSingleRecord = false;
    protected $behaviourInstances = array();
    protected $behaviours = array();

    /**
     * Prevents ntentan from running its validations. Validations may however
     * be run on the database level if necessary and these could trigger their
     * own exceptions.
     * @var boolean
     */
    public $skipValidations = false;

    public function __construct()
    {
        $modelInformation = new ReflectionObject($this);
        $last = explode("\\", $modelInformation->getName());
        $modelName = end($last);
        $this->name = strtolower(Ntentan::deCamelize($modelName));
        $this->route = implode(".", array_slice(explode("\\", $modelInformation->getName()), count(explode("/", Ntentan::$namespace)) + 1, -1));

        $this->iteratorPosition = 0;

        $dataStoreParams = Ntentan::getDefaultDataStore();
        $dataStoreClass = $dataStoreParams['datastore_class'];

        if (class_exists($dataStoreClass))
        {
            $dataStore = new $dataStoreClass($dataStoreParams);
            $this->dataStore = $dataStore;
            $this->init();
            $this->dataStore->setModel($this);
        }
        else
        {
            throw new exceptions\DataStoreException("Datastore {$dataStoreClass} doesn't exist.");
        }

        foreach ($this->behaviours as $behaviour)
        {
            $this->addBehaviour($behaviour);
        }
    }

    protected function init()
    {
        
    }

    public static function getNew()
    {
        $class = get_called_class();
        return new $class();
    }

    public function addBehaviour()
    {
        $arguments = func_get_args();
        $behaviour = array_shift($arguments);
        $behaviourClass = "\\ntentan\\models\\behaviours\\$behaviour\\" . Ntentan::camelize($behaviour) . "Behaviour";
        $this->behaviourInstances[$behaviour] = new $behaviourClass();
        $this->behaviourInstances[$behaviour]->init($this);
    }

    public static function getBelongsTo($belongsTo)
    {
        return Ntentan::plural(is_array($belongsTo) ? $belongsTo[0] : $belongsTo);
    }

    public static function getClassName($className)
    {
        $key = "model_class_$className";
        if (Cache::exists($key))
        {
            $return = Cache::get($key);
        }
        else
        {
            $classNameArray = explode('.', $className);
            $className = Ntentan::camelize(end($classNameArray));
            $return = "\\" . str_replace("/", "\\", Ntentan::$namespace) . "\\modules\\" . implode("\\", $classNameArray) . "\\$className";
            /* $modelClassFile = Ntentan::$modulesPath . '/modules/' . implode('/', $classNameArray) . "/$className.php" ;
              if(!file_exists($modelClassFile))
              {
              throw new ModelNotFoundException("Model class *$return* not found");
              } */
        }
        return $return;
    }

    /**
     *
     * @todo Implement caching for this like how the commalise has been done
     * @param type $modelField
     * @return type 
     */
    public static function splitName($modelField)
    {
        $modelArray = explode('.', $modelField);
        $return = array();
        $return['field'] = array_pop($modelArray);
        $return['model'] = implode('.', $modelArray);

        return $return;
    }

    public static function extractModelName($modelField)
    {
        $split = self::splitName($modelField);
        return $split['model'];
    }

    public static function extractFieldName($modelField)
    {
        $split = self::splitName($modelField);
        return $split['field'];
    }

    /**
     * Loads a model.
     * @param string $model
     * @return Model
     */
    public static function load($modelRoute)
    {
        if ($modelRoute == '')
            throw new ModelNotFoundException('Model route is empty');
        $className = Model::getClassName($modelRoute);
        return new $className();
    }

    public function setData($data, $overwrite = false)
    {
        if ($overwrite === true)
        {
            if (count($this->data) > 0)
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
            if (is_array($data))
            {
                foreach ($data as $field => $value)
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
        foreach ($this->hasMany as $related)
        {
            if ($related == $modelType)
                return Model::RELATIONSHIP_HAS_MANY;
        }

        foreach ($this->belongsTo as $related)
        {
            if ($related == $modelType)
                return Model::RELATIONSHIP_BELONGS_TO;
            if ($related[0] == $modelType)
                return Model::RELATIONSHIP_BELONGS_TO;
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public static function begin()
    {
        $class = get_called_class();
        return new ModelQuery(new $class());
    }

    public function get($type = null, $params = null)
    {
        $params["type"] = $type;
        $result = $this->dataStore->get($params);
        $result = $this->postGetCallback($result, $type);

        if (is_object($result))
        {
            if ($type == 'first')
            {
                $result->hasSingleRecord = true;
            }
            else
            {
                $result->hasSingleRecord = false;
            }
        }
        return $result;
    }

    protected function postGetCallback($results, $type)
    {
        return $results;
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

    public function onCommitCallback($mode)
    {
        
    }

    public function save()
    {
        $this->dataStore->begin();
        foreach ($this->behaviourInstances as $behaviour)
        {
            $behaviour->preSave($this->data);
        }
        $this->preSaveCallback();
        if ($this->validate())
        {
            $this->dataStore->setModel($this);
            $id = $this->dataStore->put();
            $this->id = $id;
            foreach ($this->behaviourInstances as $behaviour)
            {
                $behaviour->postSave($this->data);
            }
            $this->postSaveCallback($id);
            $this->dataStore->end();
            $this->onCommitCallback(self::ON_COMMIT_SAVE);
            return $id;
        }
        else
        {
            return false;
        }
    }

    public function update()
    {
        $this->dataStore->begin();
        foreach ($this->behaviourInstances as $behaviour)
        {
            $behaviour->preUpdate($this->data);
        }
        $this->preUpdateCallback();
        if (!$this->validate())
        {
            $errorsFound = false;
            foreach ($this->invalidFields as $field => $errors)
            {
                if (isset($this->data[$field]))
                {
                    $errorsFound = true;
                }
                else
                {
                    unset($this->invalidFields[$field]);
                }
            }
            if ($errorsFound)
                return false;
        }

        $this->dataStore->setModel($this);
        $this->dataStore->update();
        foreach ($this->behaviourInstances as $behaviour)
        {
            $behaviour->preUpdate($this->data);
        }
        $this->postUpdateCallback();
        $this->dataStore->end();
        $this->onCommitCallback(self::ON_COMMIT_UPDATE);
        return true;
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
        //@todo Convert all these if conditions into one huge regular expression

        if (preg_match("/(get)((?<just>Just)?(?<type>First|All|Count|[0-9]+)?((With)(?<field>[a-zA-Z0-9]+))?)?/", $method, $matches))
        {
            $field = Ntentan::deCamelize($matches['field']);
            $type = strtolower($matches['type'] == '' ? 'all' : $matches['type']);
            $conditions = array();
            foreach ($arguments as $argument)
            {
                if (is_array($argument))
                {
                    $params = $argument;
                    break;
                }
                else
                {
                    $conditions[$this->route . "." . $field] = $argument;
                }
            }

            if (count($conditions) > 0)
            {
                $params["conditions"] = is_array($params['conditions']) ? array_merge($conditions, $params['conditions']) : $conditions;
            }

            if ($matches['just'] == 'Just')
            {
                $params["fetch_related"] = false;
                $params["fetch_belongs_to"] = false;
            }
            else
            {
                if (!isset($params["fetch_related"]))
                    $params["fetch_related"] = true;
                if (!isset($params["fetch_belongs_to"]))
                    $params["fetch_belongs_to"] = true;
            }

            if (isset($params['limit']))
            {
                $type = $params['limit'];
            }

            return $this->get($type, $params);
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
        if (isset($this->data[$variable]))
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
        if (is_array($this->data[$offset]))
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
        if (is_null($offset))
        {
            $this->data[] = $value;
        }
        else
        {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function count()
    {
        return $this->count;
    }

    public function countAllItems()
    {
        return $this->dataStore->countAllItems();
    }

    public function rewind()
    {
        $this->iteratorPosition = 0;
    }

    public function current()
    {
        $newModel = clone $this;
        $newModel->setData($this->data[$this->iteratorPosition], true);
        $newModel->hasSingleRecord = true;
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

    private function addUniqueConstraint(&$description, $constraint)
    {
        foreach ($constraint['fields'] as $newColumn)
        {
            foreach ($description['unique'] as $id => $unique)
            {
                if (array_search($newColumn, $unique['fields']) !== false)
                {
                    $description['unique'][$id] = $constraint;
                    return;
                }
            }
        }
        $description['unique'][] = $constraint;
    }

    private function markForcedUniqueFields(&$description)
    {
        if (!is_array($this->mustBeUnique))
            return;

        foreach ($this->mustBeUnique as $unique)
        {
            if (is_string($unique))
            {
                $this->addUniqueConstraint($description, array(
                    'fields' => array($unique)
                        )
                );
            }
            else if (is_array($unique))
            {
                $this->addUniqueConstraint($description, array(
                    'fields' => isset($unique['field']) ? array($unique['field']) : $unique['fields'],
                    'message' => $unique['message']
                        )
                );
            }
        }
    }

    private function markBelongsToField(&$description, $params)
    {
        $i = $params['field_name'];
        if (isset($description['fields'][$params['field_name']]))
        {
            $description["fields"][$i]["model"] = Ntentan::plural($params['belongs_to_model']);
            $description["fields"][$i]["foreign_key"] = true;
            $description["fields"][$i]["field_name"] = $params['field_name'];
            if ($params['alias'] != '')
            {
                $description["fields"][$i]["alias"] = $params['alias'];
            }
        }
    }

    private function addBelongsToFields(&$description)
    {
        if (is_array($this->belongsTo))
        {
            foreach ($this->belongsTo as $belongsTo)
            {
                $belongsToModel = is_array($belongsTo) ? $belongsTo[0] : $belongsTo;
                $description["belongs_to"][] = $belongsToModel;
                if (is_array($belongsTo))
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
                $this->markBelongsToField(
                        $description, array(
                    'field_name' => $fieldName,
                    'alias' => $alias,
                    'belongs_to_model' => $belongsToModel
                        )
                );
            }
        }
        else if ($this->belongsTo != null)
        {
            $description["belongs_to"][] = $this->belongsTo;
            $fieldName = strtolower(Ntentan::singular($this->belongsTo)) . "_id";
            $this->markBelongsToField(
                    $description, array(
                'field_name' => $fieldName,
                'alias' => null,
                'belongs_to_model' => $this->belongsTo
                    )
            );
        }
    }

    public function describe()
    {
        if (!Cache::exists("model_" . $this->route))
        {
            $description = $this->dataStore->describe();
            $this->markForcedUniqueFields($description);
            $this->addBelongsToFields($description);
            Cache::add("model_" . $this->route, $description);
        }
        return Cache::get("model_" . $this->route);
    }

    public function __toString()
    {
        if (is_string($this->data))
        {
            return $this->data;
        }
        else if (is_array($this->data))
        {
            return json_encode($this->toArray(), true);
        }
    }

    private function getStdObject($data = null)
    {
        $keys = array_keys($data);

        if ($keys[0] == '0')
        {
            $returnData = array();
            foreach ($data as $index => $row)
            {
                $returnData[$index] = $this->getStdObject($row);
            }
        }
        else
        {
            foreach ($data as $field => $value)
            {
                $returnData->{$field} = $value;
            }
        }
        return $returnData;
    }

    public function toStdObject()
    {
        return $this->getStdObject($this->toArray());
    }

    public function toArray()
    {
        $data = $this->getData();
        if (!is_array($data))
            return null;
        $keys = array_keys($data);

        $returnData = array();

        if ($keys[0] == '0')
        {
            foreach ($data as $index => $row)
            {
                foreach ($row as $key => $value)
                {
                    $returnData[$index][$key] = is_object($value) ? $value->toArray() : $value;
                }
            }
        }
        else
        {
            foreach ($data as $key => $row)
            {
                if (is_object($data[$key]))
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

    public function validate()
    {
        //call the client validator

        $description = $this->describe();
        $this->invalidFields = array();

        foreach ($description["fields"] as $field)
        {
            $fieldName = $field["name"];
            if ($field["primary_key"])
                continue;

            // Validate Required
            if (($this->data[$fieldName] === "" || $this->data[$fieldName] === null) && $field["required"])
            {
                $this->invalidFields[$fieldName][] = isset($this->requiredViolationMessages[$fieldName]) ? $this->requiredViolationMessages[$fieldName] : "This field is required";
            }

            // Validate unique
            //@todo find a better way to validate composite unique keys
            if ($field["unique"] === true && ($this->data[$field["name"]] != $this->previousData[$field["name"]]))
            {
                $value = $this->get(
                                'first', array(
                            "conditions" => array(
                                $field["name"] => $this->data[$field["name"]]
                            )
                                )
                        )->toArray();

                if (count($value))
                {
                    $this->invalidFields[$fieldName][] = isset($this->uniqueViolationMessages[$fieldName]) ? $this->uniqueViolationMessages[$fieldName] : "This field must be unique";
                }
            }
        }
        return $this->isValid();
    }

    protected function isValid()
    {
        if (count($this->invalidFields) == 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function __destruct()
    {
        $this->dataStore = null;
    }

}
