<?php
namespace ntentan\views\template_engines\php;

class Variable implements \ArrayAccess, \Iterator
{
    private $keys;
    private $position;
    private $data;
    
    public static function initialize($data)
    {
        $type = gettype($data);
        switch ($type)
        {
            case 'string':
                return new Variable($data);
                
            case 'array':
                return new Variable($data, array_keys($data));
                
            case 'object':
                if(is_a($data, "\\ntentan\\views\\template_engines\\php\\Variable"))
                {
                    return $data;
                }
                else
                {
                    $reflection = new \ReflectionObject($data);
                    \ntentan\Ntentan::error("Cannot handle the {$reflection->getName()} type in templates");
                }
                
            case 'boolean':
            case 'integer':
            case 'NULL':
                return $data;
                
            default:
                \ntentan\Ntentan::error("Cannot handle the $type type in templates");
        }
    }


    public function __construct($data, $keys = array())
    {
        $this->data = $data;
        $this->keys = $keys;
    }
    
    public function __toString()
    {
        return Janitor::cleanHtml($this->data);
    }
    
    public function unescape()
    {
        return $this->data;
    }
    
    public function rewind() 
    {
        return $this->position = 0;
    }

    public function valid() 
    {
        return isset($this->data[$this->keys[$this->position]]);
    }    

    public function current() 
    {
        return Variable::initialize($this->data[$this->keys[$this->position]]);
    }

    public function key() 
    {
        return $this->keys[$this->position];
    }

    public function next() 
    {
        $this->position++;
    }

    public function offsetExists($offset) 
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) 
    {
        return Variable::initialize($this->data[$offset]);
    }

    public function offsetSet($offset, $value) 
    {
        if(is_null($offset))
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
}
