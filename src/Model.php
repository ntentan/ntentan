<?php

namespace ntentan;

use ntentan\nibii\RecordWrapper;
use ntentan\atiaa\Driver;

/**
 * An extension of the nibii\RecordWrapper which contains specific extensions to make models in ntentan easier to use.
 *
 * @method static fetch
 */
class Model extends RecordWrapper
{
    /**
     * Loads a model described by a string.
     * @param string $name
     * @return nibii\RecordWrapper
     * @throws nibii\exceptions\NibiiException
     * @todo Rewrite this so it works on its own here
     */
    public static function load($name)
    {
        return nibii\ORMContext::getInstance()->load($name);
    }

    /**
     * Create a new instance of this Model
     * @return \ntentan\nibii\RecordWrapper
     */
    public static function createNew()
    {
        $class = get_called_class();
        $instance = new $class();
        $instance->initialize();
        return $instance;
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::createNew(), $name], $arguments);
    }

    /**
     * Get a descriptive name for the model.
     * Names are usually deduced from the class name of the underlying model.
     * @return string
     * @throws \ReflectionException
     */
    public function getName()
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    protected function addError(&$array, $field, $error)
    {
        if (!isset($array[$field])) {
            $array[$field] = [];
        }
        $array[$field][] = $error;
    }

    public function getTable()
    {
        $dbStore = $this->getDBStoreInformation();
        return "{$dbStore['quoted_table']}";
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->getAdapter()->getDriver();
    }

    public function __serialize()
    {
        return [
            'data' => serialize($this->modelData),
            'table' => $this->table,
            'schema' => $this->schema,
            'hasMany' => $this->hasMany,
            'belongsTo' => $this->belongsTo,
            'manyHaveMany' => $this->manyHaveMany
        ];
    }

    public function __unserialize($data)
    {
        $this->modelData = unserialize($data['data']);
        $this->table = $data['table'];
        $this->schema = $data['schema'];
        $this->hasMany = $data['hasMany'];
        $this->belongsTo = $data['belongsTo'];
        $this->manyHaveMany = $data['manyHaveMany'];
        $this->initialize();
    }

    public function count($query = null): int
    {
        if (isset($this) && $this instanceof self) {
            return parent::count($query);
        } else {
            return self::__callStatic('count', [$query]);
        }
    }
}
