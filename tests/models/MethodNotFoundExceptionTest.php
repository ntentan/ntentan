<?php
ini_set("include_path", "../models".PATH_SEPARATOR."../../models".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/ekow/Projects/ntentan/models/MethodNotFoundException.php';

/**
 * Test class for MethodNotFoundException.
 * Generated by PHPUnit on 2010-08-12 at 08:44:27.
 */
class MethodNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MethodNotFoundException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new MethodNotFoundException;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}
?>
