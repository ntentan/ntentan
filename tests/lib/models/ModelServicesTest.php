<?php
ini_set("include_path", "../lib/models".PATH_SEPARATOR."../../../lib/models".PATH_SEPARATOR.ini_get("include_path"));
require_once 'PHPUnit/Framework.php';

require_once '/home/ekow/Projects/ntentan/lib/models/ModelServices.php';

/**
 * Test class for ModelServices.
 * Generated by PHPUnit on 2010-08-12 at 08:44:34.
 */
class ModelServicesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ModelServices
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ModelServices;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testSetModel().
     */
    public function testSetModel()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidator_required().
     */
    public function testValidator_required()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidator_unique().
     */
    public function testValidator_unique()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidator_regexp().
     */
    public function testValidator_regexp()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
?>