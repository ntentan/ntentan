<?php
abstract class SqlDatabaseDataStore extends DataStore
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
        $this->table = end(explode(".", $model->modelPath));
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
                $fieldList = $this->model->describe();
                $fieldList = array_keys($fieldList["fields"]);
                
                foreach($fieldList as $key => $field)
                {
                    $fieldList[$key] = $this->table . "." . $field;
                }
                
                $fields = implode(", ", $fieldList);
            }
            else
            {
                $fields = implode(", ", is_array($params["fields"]) ? $params["fields"] : explode(",", $params["fields"]));
            }
        }

        // Generate the base query
        $query = "SELECT $fields FROM {$this->table} ";
        
        // Generate joins
        foreach($this->model->belongsTo as $relatedModel)
        {
            if(is_array($relatedModel))
            {
                $firstRelatedModel = Model::load($relatedModel[0]);
                $firstDatastore = $firstRelatedModel->getDataStore(true);
                $secondRelatedModel = Model::load($relatedModel["through"]);
                $secondDatastore = $secondRelatedModel->getDataStore(true);
                $query .= "JOIN {$firstDatastore->table} ON {$firstDatastore->table}.id = {$secondDatastore->table}." . Ntentan::singular($firstDatastore->table) . "_id ";
            }
            else
            {
                $model = Model::load($relatedModel);
                $datastore = $model->getDataStore(true);
                $query .= "JOIN {$datastore->table} ON {$datastore->table}.id = {$this->table}." . Ntentan::singular($datastore->table) . "_id ";
            }
        }

        // Generate conditions
        if($params["conditions"] !== null)
        {
            // Go through the array of conditions and generate a condition
            // statement for MySQL
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
                $query .= " ORDER BY " . implode(",", $params['sort']);
            } else {
                $query .= " ORDER BY {$params["sort"]} ";
            }
        }

        // Add the limiting clauses
        if($params["type"] == 'first')
        {
        	$query .= "LIMIT 1";
        }
        else if(is_numeric($params["type"]))
        {
        	$query .= "LIMIT {$params["type"]}";
        }

        // Execute the query
        $result = $this->query($query);

        // Retrieve all related data
        if($params["fetch_related"] === true)
        {
            foreach($this->model->belongsToModelInstances as $key => $belongsToInstance)
            {
                $relationName = Ntentan::singular(
                    Model::getBelongsTo(
                        is_array($this->model->belongsTo) ? 
                        $this->model->belongsTo[$key] :
                        $this->model->belongsTo
                    )
                );
                $foreignKey = $relationName . "_id";
                foreach($result as $key => $row)
                {
                    $reference = $belongsToInstance->getFirstWithId($row[$foreignKey]);
                    $result[$key][$relationName] = $reference;
                }
            }
        }
        

        // Generate the data to be returned
        if($params["type"] == 'first')
        {
            $return = $result[0];
        }
        else if($params["type"] == 'count')
        {
            $return = reset($result[0]);
        }
        else
        {
            $return = $result;
        }

        return $return;
    }

    protected function _put($data)
    {
        $fields = array_keys($data);
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
        }
        else
        {
            //$fields = array_keys($data);
            foreach($data as $value)
            {
                $values[] = $value === "" ? "NULL" : "'" . $this->escape($value) . "'";
            }
            $query = "INSERT INTO {$this->table} (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $values) . ")";
        }
        return $this->query($query); 
        
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
            $values[] = "`$field` = '".MysqlDataStore::$db->escape_string($value)."'";
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
}

