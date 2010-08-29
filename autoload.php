<?php
error_reporting(E_ALL ^ E_NOTICE);

function __autoload($class)
{
    require_once end(explode("\\", $class)) . ".php";
}
