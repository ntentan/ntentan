<?php
/**
 * Source file for the abstract sql database orm driver.
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


namespace ntentan\models\datastores;

use ntentan\Ntentan;
use ntentan\models\Model;
use ntentan\models\exceptions\DataStoreException;
use ntentan\utils\Logger;
use ntentan\caching\Cache;

/**
 * A class used as the base class for datastore classes which store their data in SQL
 * Databases. This class generates standard SQL queries through which most
 * SQL database systems could be manipulated. For system specific functions (like
 * connecting, interpreting queries, escaping strings etc.) this class exposes
 * abstract methods which need to be implemented by the actual datastore classes.
 * If a datastore needs to be written for any database system which supports
 * standard SQL queries, the this class would be a great foundation to build
 * upon
 *
 * @author jainooson@gmail.com
 */
abstract class SqlDatabase extends DataStore
{
    protected $_table;
    protected $quotedTable;
    protected $_schema;
    protected $quotedSchema;
    protected $schemaDescription;
    protected $tables;
    public static $logQueries;
    protected $numRows;    
    public $lastQuery;

    public function __construct($parameters)
    {
        $this->connect($parameters);
    }

    public function __set($property, $value) 
    {
        switch($property) {
        case "table":
            $this->setTable($value);
            break;
        case "schema":
            $this->setSchema($value);
            break;
        }
    }

    public function __get($property) 
    {
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
     * @param string $table
     */
    protected function setTable($table) 
    {
        $this->_table = $table;
        $this->quotedTable = $this->quote($table);
    }

    protected function setSchema($schema) 
    {
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
        
        if($this->_table != '') return;
                
        //Detect a new schema to override the default schema for the application
        $path = explode('.', $this->model->getRoute());
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

        $cacheKey = 'model_table_' . $model->getRoute();
        if(!Cache::exists($cacheKey))
        {
            do
            {
                $this->table = implode("_", $path);
                try
                {
                    $this->describe();
                    break;
                }
                catch(\ntentan\atiaa\TableNotFoundException $e)
                {
                    $this->description = false;
                    array_shift($path);
                }
            }
            while(count($path)  > 0);
            Cache::add($cacheKey, $this->table);
        }
        else
        {
            $this->table = Cache::get($cacheKey);
        }
        
        if($this->table == null)
        {
            throw new DataStoreException("Suitable database table not found for model *{$this->model->getName()}*");
        }
    }

    protected function resolveName($fieldPath)
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
    
    /**
     * A simple function to allow for the formatting of the names of any
     * related models. If the $params['expand_related_model_names'] is true
     * then models located in packages such as 'package.model' would be
     * returned as 'model'. If this value is false however, the model
     * name is returned having all dots replaced with underscores. Please
     * note that the result of this function may be cached for performace
     * purposes.
     * 
     * @param $relatedModelName string
     * @param $params array
     * @return string
     * @todo Implement caching of this functions operations
     */
    protected function formatRelatedModelName($relatedModelName, $params)
    {
        if($params['expand_related_model_names'] === true)
        {
            return str_replace('.', '_', $relatedModelName);
        }
        else
        {
        	$array = explode('.', $relatedModelName);
            return end($array);
        }
    } 
    
    private function parseConditions($conditionsParameter, $params, $parserVars = array("depth"=>0))
    {
        $parserVars['depth']++;
        $query = '';
        if($conditionsParameter !== null && is_array($conditionsParameter))
        {
            // Go through the array of conditions and generate an SQL condition
            foreach($conditionsParameter as $field => $condition)
            {
                if($params["fetch_related"] === true || $params["fetch_belongs_to"] === true)
                {
                    $modelName = Model::extractModelName($field);
                    if($this->model->getRelationshipWith($modelName) == Model::RELATIONSHIP_HAS_MANY)
                    {
                        $parserVars["has_many_conditions"][$modelName][$field] = $condition;
                        continue;
                    }
                }
                
                if(($field == "OR" || $field == "__OR__") && is_array($condition))
                {
                    $parserVars['glue'] = 'OR';
                    $parserResults = $this->parseConditions($condition, $params, $parserVars);
                    $conditions[] = $parserResults['query'];
                    continue;
                }
                
                if(is_array($condition))
                {
                    $databaseField = $this->resolveName($field);
                    foreach($condition as $clause)
                    {
                        $orConditions[] = "$databaseField = '$clause'";
                    }
                }
                else
                {
                    preg_match("/(?<field>[a-zA-Z1-9_.]*)\w*(?<operator>\>=|\<=|\<\>|\<|\>)?/", $field, $matches);
                    $databaseField = $this->resolveName($matches["field"]);                    
                    
                    if($condition === null && $matches['operator'] == '<>')
                    {
                        $operator = 'IS NOT';
                    }
                    else if($condition === null)
                    {
                        $operator = 'IS';
                    }
                    else
                    {
                        $operator = $matches["operator"]==""?"=":$matches["operator"];
                    }
                    
                    $condition = $condition === null ? 'NULL' : "'" . $this->escape($condition) . "'";
                    $conditions[] = "$databaseField $operator $condition";
                }
            }
            
            if($parserVars['depth'] == 1)
            {
                if(is_array($conditions))
                {
                    $query .= " WHERE " . implode(" AND ", $conditions);
                }

                if(is_array($orConditions))
                {
                    $query .= (is_array($conditions) ? ' AND ' : ' WHERE ') . "(" . implode(" OR ", $orConditions) . ")";
                }
                $parserVars['query'] = $query;
            }
            elseif(isset($parserVars['glue']))
            {
                $query = "(" . implode($parserVars['glue'], $conditions) . ")";
                $parserVars['query'] = $query;
                unset($parserVars['glue']);
            }
            
            return $parserVars;
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
                $requestedFields = array_keys($description["fields"]);
            }
            else
            {
                $requestedFields = is_array($params["fields"]) ? $params["fields"] : explode(",", $params["fields"]);
            }
            
            $fields = array();
            
            $hasManyFields = array();
            $belongsToFields = array();
            
            foreach($requestedFields as $index => $field)
            {
                if($params["fetch_related"] === true || $params["fetch_belongs_to"] === true)
                {
                    $modelName = Model::extractModelName($field);
                    if($modelName != '')
                    {
                        $relationShip = $this->model->getRelationshipWith($modelName);
                        if($relationShip == Model::RELATIONSHIP_HAS_MANY)
                        {
                            $hasManyFields[$modelName][] = $field;
                            continue;
                        }
                        else if($relationShip == Model::RELATIONSHIP_BELONGS_TO)
                        {
                            $belongsToFields[$modelName][] = Model::extractFieldName($field);
                            continue;
                        }
                    }
                }

                $fields[$index] = $this->resolveName($field, true, $description);
                if($params["fetch_belongs_to"] && $description["fields"][$field]["foreign_key"] && $description["fields"][$field]["alias"] != '')
                {
                    $fields[$index] .= " AS {$description["fields"][$field]["alias"]}";
                }
            }
            $fields = implode(", ", is_array($fields) ? $fields : explode(",", $fields));
        }

        // Generate joins
        $joins = "";
        
        // Related joins from the model description
        if($params["fetch_related"] === true || $params["fetch_belongs_to"] === true)
        {
            $numRequestedBelongsTo = count($belongsToFields);
            foreach($this->model->belongsTo as $relatedModel)
            {   
                if(is_array($relatedModel) && isset($relatedModel["through"]))
                {
                    $firstRelatedModel = Model::load(Model::getBelongsTo($relatedModel[0]));
                    $firstDatastore = $firstRelatedModel->dataStore;
                    $secondRelatedModel = Model::load($relatedModel["through"]);
                    $secondDatastore = $secondRelatedModel->dataStore;
                    $joins .= " LEFT JOIN {$firstDatastore->table} ON {$firstDatastore->table}.id = {$secondDatastore->table}." . Ntentan::singular($firstDatastore->table) . "_id ";
                    
                    $joinedModelDescription = $firstRelatedModel->describe();
                    $joinedModelFields = array_keys($joinedModelDescription["fields"]);
                    
                    foreach($joinedModelFields as $index => $field)
                    {
                        $joinedModelFields[$index] =
                            $this->quote($firstDatastore->table)
                             . "." . $this->quote($field) . " AS "
                             . $this->quote($firstRelatedModel->getRoute() . ".$field");
                    }
                    
                    if($params['type'] != 'count')
                    {
                        $fields = $fields . ", " . implode(", ", $joinedModelFields);
                    }                    
                }
                else
                {
                    $alias = null; $as = null;
                    if(is_array($relatedModel))
                    {
                        $alias = isset($relatedModel['alias']) ? $relatedModel['alias'] : $relatedModel["as"];
                        $as = $relatedModel['as'];
                        $relatedModel = $relatedModel[0];
                    }

                    // If the related belongs to field was not queried then skip this whole step entirely
                    if($numRequestedBelongsTo > 0 && isset($belongsToFields[$relatedModel]))
                    {
                        $model = Model::load(Model::getBelongsTo($relatedModel));
                        $datastore = $model->dataStore;
                        $joinedModelDescription = $model->describe();
                        $joinedModelFields = $belongsToFields[$relatedModel];
                    }
                    else
                    {
                        if(is_array($requestedFields))
                        {
                            $array = explode('.', $relatedModel);
                            if($alias != null && array_search($alias, $requestedFields) === false)
                            {
                                continue;
                            }
                            else if($alias == null && array_search(Ntentan::singular(end($array)) . "_id", $requestedFields) === false)
                            {
                                continue;
                            }
                        }
                        $model = Model::load(Model::getBelongsTo($relatedModel));
                        $datastore = $model->dataStore;
                        $joinedModelDescription = $model->describe();
                        $joinedModelFields = array_keys($joinedModelDescription["fields"]);
                    }
                    
                    if($alias == null)
                    {
                        $joinedTable = $joinedModelDescription["name"];
                    }
                    else
                    {
                        $joinedTable = "{$datastore->table}_as_" . str_replace('.', '_', $alias);
                    }
                    
                    foreach($joinedModelFields as $index => $field)
                    {
                        $joinedModelFields[$index] = "{$joinedTable}.{$field} AS "
                            . $this->quote($alias =='' ? "{$model->getRoute()}.$field" : "$alias.$field");
                        /*$datastore->resolveName($field, true, $joinedModelDescription, false). " AS "
                        . $this->quote($alias =='' ? "{$model->getRoute()}.$field" : "$alias.$field");*/
                    }
                    
                    if($params['type'] != 'count')
                    {
                        $fields = $fields . ", " . implode(", ", $joinedModelFields);
                    }
                    
                    $table = $alias == '' ? $datastore->table : $joinedTable;//"{$datastore->table}_as_" $alias);
                                        
                    $joins .= " LEFT JOIN " . ($datastore->schema == "" ? '' : "{$datastore->schema}.") . $datastore->table . " "
                           .  ($alias == '' ? '' : "$table ")
                           .  " ON $table.id = {$this->table}."
                           .  ($alias != null ? $as : Ntentan::singular($datastore->table) . "_id ");
                           
                }
            }
        }

        /**
         * @todo write a test case for this
         */
        if(isset($params["through"]))
        {
            if(is_array($params["through"]))
            {
                $through = $params['through'];
            }
            else if(is_string($params['through']))
            {
                $through = explode(',', $params['through']);
            }
            
            $previousTable = $this->table;
            foreach($through as $relatedModel)
            {
                $modelInstance = Model::load($relatedModel);
                $currentTable = $modelInstance->dataStore->table;
                $foreignKey = Ntentan::singular($previousTable) . "_id";
                $joins .= " JOIN $currentTable ON $previousTable.id = $currentTable.$foreignKey ";
                $previousTable = $currentTable;
            }
        }

        // Generate the base query
        $query = "SELECT $fields FROM " . ($this->schema != '' ? $this->quotedSchema . "." :'') . $this->quotedTable . " $joins ";

        // Generate conditions
        
        $parserResults = $this->parseConditions($params['conditions'], $params);
        $hasManyConditions = $parserResults['has_many_conditions'];
        $query .= $parserResults['query'];

        // Add the sorting queries
        if(isset($params['sort']) && $params['type'] != 'count') 
        {
            if(is_array($params['sort'])) 
            {
                $query .= " ORDER BY " . implode(", ", $params['sort']);
            } 
            else 
            {
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
                            
                            if(!is_array($results[$index][$modelName])) 
                            {
                                $results[$index][$modelName] = array();
                            }
                            
                            if($params['use_dots'])
                            {
                                $results[$index]["$modelName.$fieldName"] = $value;
                            }
                            else
                            {
                                $results[$index][$modelName][$fieldName] = $value;
                            }
                            
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
                        $results[$index][$this->formatRelatedModelName($modelizedField, $params)] = $wrapperModel;
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
                            array(
                                "conditions"=>
                                    array_merge(
                                        array(
                                            Ntentan::singular($this->model->getName()) . "_id" => $result["id"]
                                        ),
                                        is_array($hasManyConditions[$hasMany]) ? $hasManyConditions[$hasMany] : array()
                                    ),
                                "fields" => $hasManyFields[$hasMany],
                                'sort' => $params["{$hasMany}_sort"],
                                'fetch_related' => $params['fetch_related'],
                                'fetch_has_many' => $params['fetch_has_many']
                            )
                        );
                        $results[$index][$this->formatRelatedModelName($hasMany, $params)] = $relatedData;
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
            $quotedFields = array();
            foreach($quotedFields as $field)
            {
                $quotedFields[] = $this->quote($field);
            }
            $query = "INSERT INTO ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->quotedTable} (".implode(", ", $quotedFields).") VALUES ";
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
            $quotedDataFields = array();
            foreach($data as $field => $value)
            {
                if(is_array($value))
                {
                    $subData[$field] = $value;
                }
                else
                {
                    $values[] = ($value === "" || $value === null ) ? "NULL" : "'".$this->escape($value) . "'";
                    $dataFields[] = $field;
                    $quotedDataFields[] = $this->quote($field);
                }
            }
            $query = "INSERT INTO ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->quotedTable} (" . implode(", ", $quotedDataFields) . ") VALUES (" . implode(", ", $values) . ")";
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

    protected function _update($data)
    {
        $description = $this->model->describe();
        $fields = array_keys($description['fields']);
        foreach($data as $field => $value)
        {
            if($field == "id") continue;
            if(is_array($value)) continue;
            if(array_search($field, $fields) === false) continue;

            if($value === null || $value === '')
            {
                $values[] = $this->quote($field) . " = null";
            }
            else
            {
                $values[] = $this->quote($field) . " = '". $this->escape($value) . "'";
            }
        }
        $query = "UPDATE ".($this->schema != '' ? $this->quotedSchema . "." :'')."{$this->quotedTable} SET " . implode(", ", $values) . " WHERE {$description['primary_key'][0]} = '{$data[$description['primary_key'][0]]}'";
        $this->query($query);
    }

    protected function _delete($key)
    {
        $relation = ($this->schema != '' ? $this->quotedSchema . "." : '') . $this->quotedTable;
        if(is_array($key))
        {
            if(count($key) > 0)
            {
                $query = "DELETE FROM $relation WHERE id in ('" . implode("','", $key) . "')";
                $this->query($query);
            }
        }
        else
        {
            $query = "DELETE FROM $relation WHERE id = '{$key}'";
            $this->query($query);
        }
    }
    
    public function doesTableExist($table, $schema)
    {
        $key = "schema_table_{$schema}_{$table}";
        if(Cache::exists($key))
        {
            return Cache::get($key);
        }
        else
        {
            $exists = $this->_doesTableExist($table, $schema);
            Cache::add($key, $exists);
            return $exists;
        }
    }
    
    public function query($query)
    {
        $this->lastQuery = $query;
        if(Ntentan::$debug === true)
        {
            if(is_writeable('logs/queries.log'))
            {
                Logger::log("[query] $query", "logs/queries.log");
            }
        }
        return $this->_query($query);
    }
    
    public function countAllItems()
    {
        $result = $this->query("SELECT COUNT(id) as count FROM {$this->schema}.{$this->table}");
        return $result[0]['count'];
    }
    
    protected function numRows()
    {
        return $this->numRows;
    }    

    protected abstract function connect($parameters);
    protected abstract function _query($query);
    public abstract function escape($query);
    public abstract function quote($field);
    protected abstract function getLastInsertId();
    protected abstract function limit($limitParams);
}
