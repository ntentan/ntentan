<?php

namespace ntentan;

class Parameters implements \ArrayAccess {

    private $parameters;

    private function __construct($parameters, $defaults) {
        $this->parameters = $parameters;
        foreach ($defaults as $key => $value) {
            if (!isset($this->parameters[$key])) {
                $this->parameters[$key] = $value;
            }
        }
    }

    public static function wrap($parameters, $defaults = []) {
        return new Parameters($parameters, $defaults);
    }

    public function get($key, $default = null) {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }
    
    public function set($key, $value) {
        $this->parameters[$key] = $value;
    }

    public function offsetExists($offset) {
        return isset($this->parameters[$offset]);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->parameters[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->parameters[$offset]);
    }

}
