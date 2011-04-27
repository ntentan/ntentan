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

namespace ntentan\models\datastores;

use ntentan\Ntentan;
use ntentan\models\Model;

/**
 * A class used as the base class datastore classes which store their data in SQL
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
    protected $quotedTable;
    protected $_schema;
    protected $quotedSchema;

    public function __construct($parameters)
    {
        $this->connect($parameters);
    }
    
    public function __set($property, $value) {
    	switch($property) {
    	case "table":
    		$this->setTable($value);
    		break;
        case "schema":
            $this->setSchema($value);
            break;
    	}
    }
    
    public function __get($property) {
    	switch($property) {
    	case "table":
    		return $this->_table;
        case 'schema':
            return $this->_schema;
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
        $this->quotedTable = $this->quote($table);
    }

    protected function setSchema($schema) {
        $this->_schema = $schema;
        $this->quotedSchema = $this->quote($schema);
    }

    /**
     * 
     * @param Model $model
     */
    public function setModel($model)
    {
        parent::setModel($model);
        //Detect a new schema to override the default schema for the application

        $path = explode('.', $this->model->modelRoute);
        $base = Ntentan::$modulesPath . '/modules';
        foreach($path as $directory)
        {
            $configFile = $base . '/' . $directory . '/config.php';
            if(file_exists($configFile))
            {
                include $configFile;
                $this->setSchema($schema);
            }
            $base .= '/' . $directory;
        }

        $this->table = end(explode(".", $model->getName()));
    }

    protected function resolveName($fieldPath, $reformat=false, $description = null)
    {
        if(strpos($fieldPath, ".") === false)
        {
            return $this->quotedTable . "." . $this->quote($fieldPath);
        }
        else
        {
            $modelPathArray = explode(".", $fieldPath);
            $fieldName = array_pop($modelPathArray);
            $modelPath = implode(".", $modelPathArray);
            $model = Model::load($modelPath);
            return $this->quote($model->dataStore->table) . '.' . $this->quote($fieldName);
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
            // If a count is not needed get a list of all the fields in the model
            $description = $this->model->describe();
            if($params["fields"] == null)
            {
                $params["fields"] = array_keys($description["fields"]);
            }
            $fields = array();
            foreach($params["fields"] as $index => $field)
            {
                $fields[$index] = $this->resolveName($field, true, $description);
                if($params["fetch_belongs_to"] && $description["fields"][$field]["foreign_key"] === true && $description["fields"][$field]["alias"] != '')
                {
                    $fields[$index] .= " AS {$description["fields"][$field]["alias"]}";
                }
            }
            $fields = implode(", ", is_array($fields) ? $fields : explode(",", $fields));
        }
        
        // Generate joins
        $joins = "";
        if($params["fetch_related"] === true || $params["fetch_belongs_to"] === true)
        {
            foreach($this->model->belongsTo as $relatedModel)
            {
                if(is_array($relatedModel) && isset($relatedModel["through"]))
                {
                    $firstRelatedModel = Model::load(Model::getBelongsTo($relatedModel[0]));
                    $firstDatastore = $firstRelatedModel->dataStore;
                    $secondRelatedModel = Model::load($relatedModel["through"]);
                    $secondDatastore = $secondRelatedModel->dataStore;
                    $joins .= " JOIN {$firstDatastore->table} ON {$firstDatastore->table}.id = {$secondDatastore->table}." . Ntentan::singular($firstDatastore->table) . "_id ";
                }
                else
                {
                    $alias = null;
                    if(is_array($relatedModel))
                    {
                        $alias = $relatedModel["as"];
                        $relatedModel = $relatedModel[0];
                    }

                    // If the related belongs to field was not queried then skip
                    if($alias != null && array_search($alias, $params["fields"]) === false) 
                    {
                        continue;
                    }
                    else if($alias == null && array_search(Ntentan::singular(end(explode('.', $relatedModel))) . "_id", $params["fields"]) === false)
                    {
                        continue;
                    }

                    $model = Model::load(Model::getBelongsTo($relatedModel));
                    $datastore = $model->dataStore;
                    $joinedModelDescription = $model->describe();
                    $joinedModelFields = array_keys($joinedModelDescription["fields"]);
                    $joinedSchema = $model->dataStore->schema;
                    
                    if($alias == null)
                    {
                        $joinedTable = $joinedModelDescription["name"];
                    }
                    else
                    {
                        $joinedTable = $alias;
                    }
                    
                    foreach($joinedModelFields as $index => $field)
                    {
                        $joinedModelFields[$index] = 
                            $this->quote($joinedTable)
                             . "." . $this->quote($field) . " AS "
                             . $this->quote($model->modelRoute . ".$field");
                    }
                    $fields = $fields . ", " . implode(", ", $joinedModelFields);
                    $joins .= " JOIN " . ($datastore->schema == "" ? '' : "{$datastore->schema}.") . $datastore->table . " "
                           . ($alias != null ? "AS $alias" : "")
                           . " ON " . ($alias != null ? $alias : $datastore->table) . ".id = {$this->table}." 
                           . ($alias != null ? $alias : Ntentan::singular($datastore->table) . "_id ");
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
                    $currentTable = $modelInstance->dataStore->table;
                    $foreignKey = Ntentan::singular($previousTable) . "_id";
                    $joins .= " JOIN $currentTable ON $previousTable.id = $currentTable.$foreignKey ";
                    $previousTable = $currentTable;
                }
            }
        }

        // Generate the base query
        $query = "SELECT $fields FROM ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->table} $joins ";

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

        // Add the limiting clauses
        if($params["type"] == 'first')
        {
        	$query .= $this->limit(array("limit"=>'1')); //" LIMIT 1";
        }
        else if(is_numeric($params["type"]))
        {
        	$query .= $this->limit(array("limit"=>$params['type'], "offset"=>$params['offset']));
        }

        $results = $this->query($query);

        // Retrieve all related data
        if($params["fetch_related"] === true || $params["fetch_belongs_to"] === true)
        {
            if(count($this->model->belongsTo) > 0)
            {
                foreach($results as $index => $result)
                {
                    $modelizedFields = array();
                    foreach($result as $field => $value)
                    {
                        if(strpos($field,".")!==false) 
                        {
                            $fieldNameArray = explode(".", $field);
                            $fieldName = array_pop($fieldNameArray);
                            $modelName = Ntentan::singular(implode(".", $fieldNameArray));
                            if(is_string($results[$index][$modelName])) $results[$index][$modelName] = array();
                            $results[$index][$modelName][$fieldName] = $value;
                            $modelizedFields[] = $modelName;
                            unset($results[$index][$field]);
                        }
                    }
                    $modelizedFields = array_unique($modelizedFields);
                    foreach($modelizedFields as $modelizedField)
                    {
                        if($description["fields"][$modelizedField]["alias"])
                        {
                            $wrapperModelName = $description["fields"][$modelizedField]["model"];
                        }
                        else
                        {
                            $wrapperModelName = Ntentan::plural($modelizedField);
                        }
                        $wrapperModel = Model::load($wrapperModelName);
                        $wrapperModel->setData($results[$index][$modelizedField], true);
                        $results[$index][$modelizedField] = $wrapperModel;
                    }
                }
            }
        }
        
        if($params["fetch_related"] === true || $params["fetch_has_many"] === true)
        {
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
            $query = "INSERT INTO ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->table} (".implode(",", $fields).") VALUES ";
            $baseQueries = array();
            foreach($data as $row)
            {
                $values = array();
                foreach($row as $value)
                {
                    $values[] = ($value === "" || $value === null ) ? "NULL" : "'".$this->escape($value) . "'";
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
                    $values[] = ($value === "" || $value === null)  ? "NULL" : "'" . $this->escape($value) . "'";
                    $dataFields[] = $field;
                }
            }
            $query = "INSERT INTO ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->table} (" . implode(", ", $dataFields) . ") VALUES (" . implode(", ", $values) . ")";
            $this->query($query);
            if(array_search('id', $dataFields) === false)
            {
                $id = $this->getLastInsertId();
            }
            else
            {
                $id = $data['id'];
            }
            foreach($subData as $modelName => $data)
            {
                $model = Model::load($modelName);
                $table = $model->dataStore->table;
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
        $description = $this->model->describe();
        $fields = array_keys($description['fields']);
        foreach($data as $field => $value)
        {
            if($field == "id") continue;
            if(is_array($value)) continue;
            if(array_search($field, $fields) === false) continue;

            $values[] = $this->quote($field) . " = '". $this->escape($value) . "'";
        }
        $query = "UPDATE ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->table} SET " . implode(", ", $values) . " WHERE id = '{$data["id"]}'";
        $this->query($query);
    }
    
    protected function _delete($key)
    {
        $query = "DELETE FROM {$this->table} WHERE id = '{$key}'";
        $this->query($query);
    }

    protected abstract function connect($parameters);
    public abstract function query($query);
    protected abstract function escape($query);
    protected abstract function quote($field);
    protected abstract function getLastInsertId();
    protected abstract function limit($limitParams);
    public abstract function describeModel();
    public abstract function describeTable($table, $schema);
}
