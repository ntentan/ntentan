<?php
class MethodNotFoundException extends Exception
{
    public function __construct($method)
    {
        parent::__construct("Method doesn't exist <code>$method</code>");
    }
}