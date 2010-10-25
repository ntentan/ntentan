<?php

namespace ntentan\models\datastores;

use ntentan\Ntentan;
use ntentan\models\Model;

/**
 * A class used for the writing of datastores which store their data in SQL 
 * Databases. This class generates standard SQL queries through which most 
 * SQL database systems could be manipulated. For system specific functions (like
 * connecting, interpreting queries, escaping strings etc.) this class exposes 
 * abstract methods which need to be implemented by the actual datastore classes.
 * If a datastore needs to be written for any database system which supports
 * standard SQL queries, the this class would be a great foundation to build
 * upon
 * 
 * @author jainooson@gmail.com,
 * @package ntentan.models.datastores
 * @abstract
 */
abstract class SqlDatabase extends DataStore
{
    protected $_table;
    
    protected $defaultSchema;

    public function __construct($parameters)
    {
        $this->connect($parameters);
    }
    
    public function __set($property, $value) {
    	switch($property) {
    	case "table":
    		$this->setTable($value);
    		break;
    	}
    }
    
    public function __get($property) {
    	switch($property) {
    	case "table":
    		return $this->_table;
    		break;
    	}
    }
    
    /**
     * A protected function used internally to set the table names. This function
     * exists so that datastores which need to modify the table names could do
     * so by overriding it and providing their own implementation.
     * 
     * @param unknown_type $table
     */
    protected function setTable($table) {
    	$this->_table = $table;
    }

    /**
     * 
     * @param Model $model
     */
    public function setModel($model)
    {
        parent::setModel($model);
        $this->table = end(explode(".", $model->getName()));
    }

    private function resolveName($fieldPath)
    {
        if(strpos($fieldPath, ".") === false)
        {
            return $this->table . "." . $fieldPath;
        }
        else
        {
            $modelPathArray = explode(".", $fieldPath);
            $fieldName = array_pop($modelPathArray);
            $modelPath = implode(".", $modelPathArray);
            $model = Model::load($modelPath);
            return "{$model->getDataStore(true)->table}.$fieldName";
        }
    }

    protected function _get($params)
    {
        // Get a list of fields convert it to a count if that is what is needed
        if($params["type"] == "count")
        {
            $fields = "COUNT(*)";
        }
        else
        {
            if($params["fields"] == null)
            {
                //$fields = " * ";
                $description = $this->model->describe();
                $modelFields = array_keys($description["fields"]);
                foreach($modelFields as $index => $field)
                {
                    $modelFields[$index] = $this->quote($description["name"]). "." . $this->quote($field);
                }
                $fields = implode(", ", $modelFields);
            }
            else
            {
                $fields = implode(", ", is_array($params["fields"]) ? $params["fields"] : explode(",", $params["fields"]));
            }
        }
        
        // Generate joins
        $joins = "";
        if($params["fetch_related"] === true)
        {
            foreach($this->model->belongsTo as $relatedModel)
            {
                if(is_array($relatedModel))
                {
                    $firstRelatedModel = Model::load(Model::getBelongsTo($relatedModel[0]));
                    $firstDatastore = $firstRelatedModel->getDataStore(true);
                    $secondRelatedModel = Model::load($relatedModel["through"]);
                    $secondDatastore = $secondRelatedModel->getDataStore(true);
                    $joins .= " JOIN {$firstDatastore->table} ON {$firstDatastore->table}.id = {$secondDatastore->table}." . Ntentan::singular($firstDatastore->table) . "_id ";
                }
                else
                {
                    $model = Model::load(Model::getBelongsTo($relatedModel));
                    $datastore = $model->getDataStore(true);
                    $joinedModelDescription = $model->describe();
                    $joinedModelFields = array_keys($joinedModelDescription["fields"]);
                    foreach($joinedModelFields as $index => $field)
                    {
                        $joinedModelFields[$index] = 
                            $this->quote($joinedModelDescription["name"])
                             . "." . $this->quote($field) . " as "
                             . $this->quote("{$joinedModelDescription["name"]}.$field");
                    }
                    $fields = $fields . ", " . implode(", ", $joinedModelFields);
                    $joins .= " JOIN {$datastore->table} ON {$datastore->table}.id = {$this->table}." . Ntentan::singular($datastore->table) . "_id ";
                }
            }
        }
        
        if(isset($params["through"]))
        {
            if(is_array($params["through"]))
            {
                $previousTable = $this->table;
                foreach($params["through"] as $relatedModel)
                {
                    $modelInstance = Model::load($relatedModel);
                    $currentTable = $modelInstance->getDataStore(true)->table;
                    $foreignKey = Ntentan::singular($previousTable) . "_id";
                    $joins .= " JOIN $currentTable ON $previousTable.id = $currentTable.$foreignKey ";
                    $previousTable = $currentTable;
                }
            }
        }

        // Generate the base query
        $query = "SELECT $fields FROM {$this->table} $joins ";

        // Generate conditions
        if($params["conditions"] !== null && is_array($params["conditions"]))
        {
            // Go through the array of conditions and generate an SQL condition
            foreach($params["conditions"] as $field => $condition)
            {
                if(is_array($condition))
                {
                    foreach($condition as $clause)
                    {
                        $conditions[] = "$field = '$clause'";
                    }
                }
                else
                {
                    preg_match("/(?<field>[a-zA-Z1-9_.]*)\w*(?<operator>\<\>|\<|\>|)?/", $field, $matches);
                    $databaseField = $this->resolveName($matches["field"]);
                    $conditions[] = "$databaseField ".($matches["operator"]==""?"=":$matches["operator"])." '$condition'";
                }
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        // Add the sorting queries
        if(isset($params['sort'])) {
            if(is_array($params['sort'])) {
                $query .= " ORDER BY " . implode(", ", $params['sort']);
            } else {
                $query .= " ORDER BY {$params["sort"]} ";
            }
        }
        
        if(isset($params["offset"]))
        {
            $offset = $params["offset"] . ", ";
        }

        // Add the limiting clauses
        if($params["type"] == 'first')
        {
        	$query .= "LIMIT 1";
        }
        else if(is_numeric($params["type"]))
        {
        	$query .= "LIMIT $offset {$params["type"]}";
        }

        // Execute the query
        $results = $this->query($query);

        // Retrieve all related data
        if($params["fetch_related"] === true)
        {
            if(count($this->model->belongsTo) > 0)
            {
                foreach($results as $index => $result)
                {
                    foreach($result as $field => $value)
                    {
                        if(strpos($field,".")!==false) 
                        {
                            $fieldNameArray = explode(".", $field);
                            $fieldName = array_pop($fieldNameArray);
                            $modelName = Ntentan::singular(implode(".", $fieldNameArray));
                            $results[$index][$modelName][$fieldName] = $value;
                            unset($results[$index][$field]);
                        }
                    }
                }
            }
            
            if(count($this->model->hasMany) > 0)
            {
                foreach($this->model->hasMany as $hasMany)
                {
                    foreach($results as $index => $result)
                    {
                        $model = Model::load($hasMany);
                        $relatedData = $model->get('all', 
                            array("conditions"=>
                                array(
                                    Ntentan::singular($this->model->getName()) . "_id" => $result["id"]
                                )
                            )
                        );
                        $results[$index][$hasMany] = $relatedData;
                    }
                }
            }
        }

        // Generate the data to be returned
        if($params["type"] == 'first')
        {
            $return = $results[0];
        }
        else if($params["type"] == 'count')
        {
            $return = reset($results[0]);
        }
        else
        {
            $return = $results;
        }

        return $return;
    }

    protected function _put($data)
    {
        $fields = array_keys($data);
        $subData = array();
        if($fields[0] == "0")
        {
            $fields = array_keys($data[0]);
            $query = "INSERT INTO {$this->table} (".implode(",", $fields).") VALUES ";
            $baseQueries = array();
            foreach($data as $row)
            {
                $values = array();
                foreach($row as $value)
                {
                    $values[] = $value === "" ? "NULL" : "'".$this->escape($value) . "'";
                }
                $baseQueries[] = "( ".implode(", ", $values)." )";
            }
            $query .= implode(",", $baseQueries);
            $this->query($query);
            $id = true;
        }
        else
        {
            $dataFields = array();
            foreach($data as $field => $value)
            {
                if(is_array($value))
                {
                    $subData[$field] = $value; 
                }
                else
                {
                    $values[] = $value === "" ? "NULL" : "'" . $this->escape($value) . "'";
                    $dataFields[] = $field;
                }
            }
            $query = "INSERT INTO {$this->table} (" . implode(", ", $dataFields) . ") VALUES (" . implode(", ", $values) . ")";
            $this->query($query);
            $id = $this->getLastInsertId();
            foreach($subData as $modelName => $data)
            {
                $model = Model::load($modelName);
                $table = $model->getDataStore(true)->table;
                $fields = array_keys($data[0]);
                $fields[] = Ntentan::singular($this->model->name) . "_id";
                $query = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES ";
                $dataQueries = array();
                foreach($data as $newEntry)
                {
                    $values = array();
                    foreach($newEntry as $value)
                    {
                        $values[] =  $value = "" ? "NULL" : "'" . $this->escape($value) . "'";
                    }
                    $values[] = $id;
                    $dataQueries[] = "(" . implode(", ", $values) . ")";
                }
                $query .= implode(", ", $dataQueries);
                $this->query($query);
            }
        }
        return $id;
    }

    public function getDataStoreInfo()
    {
    
    }

    protected function _update($data)
    {
        $fields = array_keys($data);
        foreach($data as $field => $value)
        {
            if($field == "id") continue;
            if(is_array($value)) continue;
            $values[] = $this->quote($field) . " = '". $this->escape($value) . "'";
        }
        $query = "UPDATE {$this->table} SET " . implode(", ", $values) . " WHERE id = '{$data["id"]}'";
        $this->query($query);
    }
    
    protected function _delete($key)
    {
        $query = "DELETE FROM {$this->table} WHERE id = '{$key}'";
        $this->query($query);
    }
    
    protected abstract function connect($parameters);
    protected abstract function query($query);
    protected abstract function escape($query);
    protected abstract function quote($field);
    protected abstract function getLastInsertId();
    public abstract function describeModel();
}
