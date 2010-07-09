<?php
class MethodNotFoundException extends Exception
{
    public function __construct($method)
    {
        parent::__construct("Method <code>[$method]</code> doesn't exist ");
    }
}