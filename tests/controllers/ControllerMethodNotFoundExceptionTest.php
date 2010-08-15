<?php
ini_set("include_path", "../controllers".PATH_SEPARATOR."../../controllers".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/ekow/Projects/ntentan/controllers/ControllerMethodNotFoundException.php';

/**
 * Test class for ControllerMethodNotFoundException.
 * Generated by PHPUnit on 2010-08-12 at 08:44:30.
 */
class ControllerMethodNotFoundExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ControllerMethodNotFoundException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ControllerMethodNotFoundException;
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