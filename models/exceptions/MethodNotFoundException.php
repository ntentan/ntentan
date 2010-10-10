<?php
namespace ntentan\models\exceptions;

use \Exception;

class MethodNotFoundException extends Exception
{
    public function __construct($method)
    {
        parent::__construct("Method <code>[$method]</code> doesn't exist ");
    }
}