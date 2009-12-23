<?php

/**
 * Template engine subclass which contains all the initial settings
 * that the smarty engine needs to work.
 */
abstract class AbstractTemplateEngine
{
    abstract function out($param1, $param2 = null);
    abstract function getOutput();
}

