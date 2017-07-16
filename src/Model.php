<?php

namespace ntentan;

use ntentan\nibii\RecordWrapper;

/**
 * An extension of the nibii\RecordWrapper which contains specific Ntentan
 * extensions.
 */
class Model extends RecordWrapper implements \Serializable
{

    /**
     * Loads a model described by a string.
     * @param string $name
     * @return \ntentan\Model
     */
    public static function load($name)
    {
        return nibii\ORMContext::getInstance(Context::getInstance()->getContainer())->load($name);
    }

    /**
     * Get a descriptive name for the model.
     * Names are usually deduced from the class name of the underlying model.
     * @return string
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

    public function getDriver()
    {
        return $this->getAdapter()->getDriver();
    }
    
    public function serialize()
    {
        return json_encode([
            'data' => $this->getData(),
            'table' => $this->table,
            'schema' => $this->schema,
            'hasMany' => $this->hasMany,
            'belongsTo' => $this->belongsTo,
            'manyHaveMany' => $this->manyHaveMany
        ]);
    }

    public function unserialize($serialized)
    {
        $unserialized = json_decode($serialized, true);
        $this->setData($unserialized['data']);
        $this->table = $unserialized['table'];
        $this->schema = $unserialized['schema'];
        $this->hasMany = $unserialized['hasMany'];
        $this->belongsTo = $unserialized['belongsTo'];
        $this->manyHaveMany = $unserialized['manyHaveMany'];
        $this->initialize();
    }

}
