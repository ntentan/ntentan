<?php
ini_set("include_path", "../views".PATH_SEPARATOR."../../views".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/ekow/Projects/ntentan/views/Presentation.php';

/**
 * Test class for Presentation.
 * Generated by PHPUnit on 2010-08-12 at 08:44:29.
 */
class PresentationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Presentation
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Presentation;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement test__get().
     */
    public function test__get()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAddHelper().
     */
    public function testAddHelper()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>
