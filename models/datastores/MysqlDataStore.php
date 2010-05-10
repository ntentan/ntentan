<?php
class MysqlDataStore extends DataStore
{
    private static $db;
    public $table;
    private static $defaultSchema;
    
    private static function connect($parameters)
    {
        MysqlDataStore::$defaultSchema = $parameters["database"];
        return new mysqli(
            $parameters["hostname"],
            $parameters["username"],
            $parameters["password"],
            $parameters["database"]
        );
    }

    public function __construct($parameters)
    {
        if(MysqlDataStore::$db == null)
        {
            MysqlDataStore::$db = MysqlDataStore::connect($parameters);
        }
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
                
                $fields = implode(",", $fieldList);
            }
            else
            {
                $fields = implode(",", $params["fields"]);
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
        if(isset($params['sort']))
        {
            if(is_array($params['sort']))
            {
                
            }
            else
            {
                $query .= " ORDER BY {$params["sort"]} ";
            }
        }

        // Add the limiting clauses
        $query .= ($params["type"] == 'first' ? " LIMIT 1" : "" );

        // 
        $queryResult = MysqlDataStore::$db->query($query);
        
        if($queryResult === false)
        {
            throw new DataStoreException ("MySQL Says : ".MysqlDataStore::$db->error);
        }
        $result = array();
        while($row = $queryResult->fetch_assoc())
        {
            $result[] = $row;
        }

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
            $query = "INSERT INTO {$this->table} (`".implode("`,`", $fields)."`) VALUES ";
            $baseQueries = array();
            foreach($data as $row)
            {
                $values = array();
                foreach($row as $value)
                {
                    $values[] = $value === "" ? "NULL" : "'". MysqlDataStore::$db->escape_string($value) . "'";
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
                $values[] = $value === "" ? "NULL" : "'" . MysqlDataStore::$db->escape_string($value) . "'";
            }
            $query = "INSERT INTO {$this->table} (`" . implode("`, `", $fields) . "`) VALUES (" . implode(", ", $values) . ")";
        }

        MysqlDataStore::$db->query($query);
        return MysqlDataStore::$db->insert_id;
        
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
        MysqlDataStore::$db->query($query);
    }
    
    protected function _delete($key)
    {
        $query = "DELETE FROM {$this->table} WHERE id = '{$key}'";
        MysqlDataStore::$db->query($query);
    }

    private function describeType($type)
    {
        preg_match('/(?<type>int|varchar|text|double)(\((?<lenght>[0-9]*)\))?[ ]?(?<signed>unsigned)?/', $type, $matches);
        switch($matches["type"])
        {
            case "int":
                $return["type"] = "integer";
                $return["signed"] = $matches["signed"] == "unsigned" ? false : true;
                break;

            case "double":
                $return["type"] = "double";
                break;
            
            case "varchar":
                $return["type"] = "string";
                $return["lenght"] = $matches["lenght"];
                break;

            case "text":
                $return["type"] = "string";
                break;
        }
        return $return;
    }
    
    protected function query($query)
    {
        $queryResult = MysqlDataStore::$db->query($query);
        
        if($queryResult === false)
        {
            throw new DataStoreException ("MySQL Says : ".MysqlDataStore::$db->error);
        }
        $result = array();
        while($row = $queryResult->fetch_assoc())
        {
            $result[] = $row;
        }
        
        return $result;
    }

    public function describe()
    {
        $fields = array();
        $result = MysqlDataStore::$db->query("DESCRIBE {$this->table}");
        if($result === false) throw new Exception("Mysql Query Error");
        while($row = $result->fetch_assoc())
        {
            $field["name"] = $row["Field"];
            $type = $this->describeType($row["Type"]);
            $field["type"] = $type["type"];

            if($type["lenght"] != null) $field["lenght"] = $type["lenght"];
            if($type["signed"]) $field["signed"] = true;
            if($row["Null"] == "NO") $field["required"] = true;
            if($row["Key"] == "PRI") $field["primary_key"] = true;
            
            $fields[$row["Field"]] = $field;
            unset($field);
        }
        
        $uniqueFields = $this->query(
            "select column_name from 
             information_schema.table_constraints pk 
             join information_schema.key_column_usage c on 
             c.table_name = pk.table_name and 
             c.constraint_name = pk.constraint_name
             where pk.table_name = '{$this->table}' 
                and pk.table_schema='" . MysqlDataStore::$defaultSchema . "'
             and constraint_type = 'UNIQUE'"
        );
        
        foreach($uniqueFields as $uniqueField)
        {
            $fields[$uniqueField["column_name"]]["unique"] = true;
        }
        
        $description["name"] = $this->table;
        $description["fields"] = $fields;
        
        return $description;
    }
}