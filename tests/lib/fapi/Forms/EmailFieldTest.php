<?php
ini_set("include_path", "../lib/fapi/Forms".PATH_SEPARATOR."../../../../lib/fapi/Forms".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/ekow/Projects/ntentan/lib/fapi/Forms/EmailField.php';

/**
 * Test class for EmailField.
 * Generated by PHPUnit on 2010-08-12 at 08:44:49.
 */
class EmailFieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EmailField
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new EmailField;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testValidate().
     */
    public function testValidate()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>