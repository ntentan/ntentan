<?php
namespace ntentan\models\datastores;

abstract class Atiaa extends SqlDatabase
{
    /**
     *
     * @var \ntentan\atiaa\Driver;
     */
    protected static $db;
    protected $description = false;

    protected function connect($parameters)
    {
        if(!is_object(self::$db))
        {
            self::$db = \ntentan\atiaa\Atiaa::getConnection(array(
                'user' => $parameters['user'],
                'password' => $parameters['password'],
                'host' => $parameters['host'],
                'dbname' => $parameters['name'],
                'driver' => $parameters['datastore']
            ));
            if($parameters['schema'] != '')
            {
                self::$db->setDefaultSchema($parameters['schema']);
            }
        }
        $this->setSchema(self::$db->getDefaultSchema());
    }
    
    public static function reset()
    {
        self::$db = null;
    }
    
    public function _query($query)
    {
        $results = self::$db->query($query);
        $this->numRows = count($results);
        return $results;
    }
    
    public function escape($string)
    {
        $escaped = self::$db->quote($string);        
        return substr($escaped, 1, strlen($escaped) - 2);
    }
    
    public function quote($string)
    {
        return self::$db->quoteIdentifier($string);
    }
    
    private function appendConstraints(&$description, $constraints, $key, $flat = false)
    {
        foreach($constraints as $constraint)
        {
            if($flat)
            {
                $description[$key] = $constraint['columns'];
                break;
            }
            else
            {
                $description[$key][] = array(
                    'fields' =>$constraint['columns']
                );
            }
        }
    }
    
    public function describe()
    {
        if($this->description === false)
        {
            $this->description = array(
                'name' => $this->model->getName(),
                'fields' => array(),
                'primary_key' => array(),
                'unique' => array()
            );
            $description = self::$db->describeTable("{$this->schema}.{$this->table}");
            $description = $description[$this->table];
            
            $this->appendConstraints($this->description, $description['primary_key'], 'primary_key', true);
            $this->appendConstraints($this->description, $description['unique_keys'], 'unique');
            
            foreach($description['columns'] as $field)
            {
                $field['required'] = !$field['nulls'];
                $field['type'] = $this->fixType($field['type']);
                unset($field['nulls']);
                unset($field['default']);
                $this->description['fields'][$field['name']] = $field;
            }            
        }
        return $this->description;
    }
    
    protected function getLastInsertId()
    {
        return self::$db->getLastInsertId();
    }    
    
    protected abstract function fixType($type);
}
