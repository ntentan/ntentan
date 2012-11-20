<?php
namespace ntentan\test_cases;

require_once 'lib/models/Model.php';
require_once 'tests/app/modules/users/Users.php';
require_once 'tests/app/modules/roles/Roles.php';
require_once 'tests/app/modules/departments/Departments.php';
require_once 'lib/Ntentan.php';
require_once 'lib/models/exceptions/ModelNotFoundException.php';
require_once 'lib/models/exceptions/DataStoreException.php';
require_once 'lib/caching/Cache.php';
require_once "lib/exceptions/MethodNotFoundException.php";

abstract class SqlDatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var DataStore
     */
    protected $users;
    protected $roles;
    protected $departments;
    protected $datastoreName;

    /**
     * Returns an instance of the datastore of the database being tested.
     */
    abstract protected function getInstance();
    
    protected function getConfigFile()
    {
        if(isset($GLOBALS['config']))
        {
            return $GLOBALS['config'];
        }
        else
        {
            return "tests/config/config.php";
        }
    }

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_XmlDataSet(
            'tests/fixtures/sqldatabase.xml'
        );
    }

    protected function getSetUpOperation()
    {
        return $this->getOperations()->CLEAN_INSERT(TRUE);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        \ntentan\caching\Cache::reset();
        $this->users = \ntentan\models\Model::load('users');
        $this->roles = \ntentan\models\Model::load('roles');
        $this->departments = \ntentan\models\Model::load('departments');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }
    
    public function testGetDefaultDatastore()
    {
        $datastore = \ntentan\Ntentan::getDefaultDataStore();
        $this->assertEquals($this->datastoreName, $datastore['datastore']);
    }

    public function testSetModel()
    {
    	$this->assertEquals($this->users->dataStore->table, "users");
        $this->assertEquals($this->roles->dataStore->table, "roles");
        $this->assertEquals($this->departments->dataStore->table, "departments");
    }

    public function testDescribe()
    {
        $rolesDescription = array(
            'name' => 'roles',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => '',
                    'unique' => true,
                    'unique_violation_message' => 'Two roles cannot have the same name'
                ),
            )
        );

        $departmentsDescription = array(
            'name' => 'departments',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'name' => array(
                    'name' => 'name',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
            )
        );

        $usersDescription = array(
            'name' => 'users',
            'fields' => array(
                'id' => array(
                    'name' => 'id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'primary_key' => true
                ),
                'username' => array(
                    'name' => 'username',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => '',
                    'unique' => true
                ),
                'password' => array(
                    'name' => 'password',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'role_id' => array(
                    'name' => 'role_id',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => '',
                    'model' => 'roles',
                    'foreign_key' => true,
                    'field_name' => 'role_id',
                    'alias' => 'role'
                ),
                'firstname' => array(
                    'name' => 'firstname',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'lastname' => array(
                    'name' => 'lastname',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'othernames' => array(
                    'name' => 'othernames',
                    'type' => 'string',
                    'required' => false,
                    'length' => 255,
                    'comment' => ''
                ),
                'status' => array(
                    'name' => 'status',
                    'type' => 'integer',
                    'required' => true,
                    'length' => null,
                    'comment' => ''
                ),
                'email' => array(
                    'name' => 'email',
                    'type' => 'string',
                    'required' => true,
                    'length' => 255,
                    'comment' => ''
                ),
                'phone' => array(
                    'name' => 'phone',
                    'type' => 'string',
                    'required' => false,
                    'length' => 64,
                    'comment' => ''
                ),
                'office' => array(
                    'name' => 'office',
                    'type' => 'integer',
                    'required' => false,
                    'length' => null,
                    'comment' => '',
                    'model' => 'departments',
                    'foreign_key' => true,
                    'field_name' => 'office',
                    'alias' => 'office'
                ),
                'last_login_time' => array(
                    'name' => 'last_login_time',
                    'type' => 'datetime',
                    'required' => false,
                    'length' => null,
                    'comment' => ''
                ),
                'is_admin' => array(
                    'name' => 'is_admin',
                    'type' => 'boolean',
                    'required' => false,
                    'length' => null,
                    'comment' => ''
                ),
            ),
            'belongs_to' => array (
                'role',
                'department'
            )
        );
        
        $this->assertEquals($this->roles->describe(), $rolesDescription);
        $this->assertEquals($this->departments->describe(), $departmentsDescription);
    }

    public function testDescribeModel()
    {
        $description = array(
            'tables' => array(
                'departments' => array(
                    'belongs_to' => array(),
                    'has_many' => array(
                        'users'
                    )
                ),
                'roles' => array(
                    'belongs_to' => array(),
                    'has_many' => array(
                        'users'
                    )
                ),
                'users' => array(
                    'belongs_to' => array(
                        'role',
                        array('department', 'as' => 'office')
                    ),
                    'has_many' => array()
                )
            )
        );

        $rolesDescription = $this->roles->dataStore->describeModel();
        $this->assertEquals($rolesDescription['roles'], $description['roles']);
        $usersDescription = $this->users->dataStore->describeModel();
        $this->assertEquals($usersDescription['users'], $description['users']);
        $departmentsDescription = $this->departments->dataStore->describeModel();
        $this->assertEquals($departmentsDescription['departments'], $description['departments']);
    }

    public function testGetName()
    {
        $this->assertEquals($this->roles->getName('roles'), 'roles');
        $this->assertEquals($this->users->getName('roles'), 'users');
        $this->assertEquals($this->departments->getName(), 'departments');
    }
    
    public function testMethodCalls()
    {
        $this->assertInstanceOf('tests\modules\roles\Roles', \tests\modules\roles\Roles::getAll());
        $this->assertInstanceOf('tests\modules\roles\Roles', $this->roles->getAll());
        $this->setExpectedException(
            'ntentan\exceptions\MethodNotFoundException'
        );
        \tests\modules\roles\Roles::someMethodBi();
        $this->setExpectedException(
            'ntentan\exceptions\MethodNotFoundException'
        );
        $this->roles->someMethodBi();
    }

    public function testGet()
    {
        $rolesTestData = $this->roles->get()->toArray();

        $this->assertContains(array('id' => '1', 'name' => 'System Administrator'), $rolesTestData);
        $this->assertContains(array('id' => '2', 'name' => 'System Auditor'), $rolesTestData);
        $this->assertContains(array('id' => '3', 'name' => 'Content Author'), $rolesTestData);
        $this->assertContains(array('id' => '4', 'name' => 'Site Member'), $rolesTestData);

        $this->assertEquals(true, is_object($this->roles->get()));
        $this->assertObjectHasAttribute('belongsTo', $this->roles->get());
        $this->assertEquals($this->roles->get('count'), '4');

        $filteredRolesData = array(
            array('id' => '1', 'name' => 'System Administrator'),
            array('id' => '2', 'name' => 'System Auditor'),
        );
        
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id<' => 3
                    )
                )
            )->toArray()
        );
        
        $filteredRolesData = array(
            array('id' => '3', 'name' => 'Content Author'),
            array('id' => '4', 'name' => 'Site Member'),
        );
        
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id>' => 2
                    )
                )
            )->toArray()
        );
        
        $filteredRolesData = array(
            array('id' => '3', 'name' => 'Content Author'),
            array('id' => '4', 'name' => 'Site Member'),
        );
        
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id>=' => 3
                    )
                )
            )->toArray()
        );
        
        $filteredRolesData = array(
            array('id' => '1', 'name' => 'System Administrator'),
            array('id' => '2', 'name' => 'System Auditor'),
            array('id' => '4', 'name' => 'Site Member'),
        );
        
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id<>' => 3
                    ),
                    'sort' => array(
                        'id'
                    )
                )
            )->toArray()
        );
        
        $filteredRolesData = array(
            array('id' => '4', 'name' => 'Site Member'),
            array('id' => '2', 'name' => 'System Auditor'),
            array('id' => '1', 'name' => 'System Administrator')
        );
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->get(
                'all', array(
                    'conditions' => array(
                        'id<>' => 3
                    ),
                    'sort' => 'id DESC'
                )
            )->toArray()
        );
        
        $filteredRolesData = array('id' => '1', 'name' => 'System Administrator');
        $this->assertEquals(
            $filteredRolesData,
            $this->roles->getJustFirstWithId(1)->toArray()
        );
        
        $roles = $this->roles->getFirstWithId(1)->toArray();
        $this->assertInternalType('array', $roles['users']);
        $this->assertEquals(3, count($roles['users']));
        $this->assertArrayHasKey('username', $roles['users'][0]);
        $this->assertArrayHasKey('password', $roles['users'][0]);
        $this->assertArrayHasKey('id', $roles['users'][0]);
        $this->assertArrayHasKey('role_id', $roles['users'][0]);
        $this->assertArrayHasKey('is_admin', $roles['users'][0]);
        
        $roles = $this->roles->getJustAll(
            array(
                'fields' => array(
                    'name'
                )
            )
        )->toArray();
        
        $this->assertContains(array('name' => 'System Administrator'), $roles);
        $this->assertContains(array('name' => 'System Auditor'), $roles);
        $this->assertContains(array('name' => 'Content Author'), $roles);
        $this->assertContains(array('name' => 'Site Member'), $roles);
        
        $roles = $this->roles->getFirstWithId(
            1,
            array(
                'fields' => array(
                    'id',
                    'name',
                    'users.username'
                )
            )
        )->toArray();
        
        $this->assertEquals(3, count($roles['users']));
        $this->assertContains(array('username' => 'odadzie'), $roles['users']);
        $this->assertContains(array('username' => 'mwembley'), $roles['users']);
        $this->assertContains(array('username' => 'eabaka'), $roles['users']);
        
        $roles = $this->roles->getJust2(
            array(
                'sort' => 'id'
            )
        )->toArray();
        
        $this->assertCount(2, $roles);
        $this->assertContains(array('id' => '2', 'name' => 'System Auditor'), $roles);
        $this->assertContains(array('id' => '1', 'name' => 'System Administrator'), $roles);
        
        $user = $this->users->getFirstWithId(1);
        $this->assertInstanceOf('tests\modules\users\Users', $user);
        $this->assertEquals('odadzie', $user->username);
        
        $this->assertInstanceOf('tests\modules\roles\Roles', $user->role);
        $user = $user->toArray();
        $this->assertContains(array('name' => 'System Administrator', 'id' => '1'), $user);
        $this->assertContains(array('name' => 'Software Developers', 'id' => '1'), $user);
        
        $user = $this->users->getFirstWithId(1,
            array(
                'fields' => array(
                    'id',
                    'username',
                    'role.name',
                    'department.name'
                )
            )
        );
        
        $this->assertInstanceOf('tests\modules\roles\Roles', $user->role);
        $this->assertInstanceOf('tests\modules\departments\Departments', $user->department);
        
        $user = $user->toArray();
        $this->assertCount(1, $user['role']);
        $this->assertCount(1, $user['department']);
    }
    
    public function testIterator()
    {
        $roles = $this->roles->getJustAll(
            array('sort' => 'id')
        );
        
        $this->assertEquals(4, $roles->count());
        $expectedRoles = array(
            array('id' => '1', 'name' => 'System Administrator'),
            array('id' => '2', 'name' => 'System Auditor'),
            array('id' => '3', 'name' => 'Content Author'),
            array('id' => '4', 'name' => 'Site Member'),
        );
        
        foreach($roles as $i => $role)
        {
            $this->assertEquals($expectedRoles[$i], $role->toArray());
        }
    }

    public function testSetData()
    {
        $this->roles->setData(
            array('name' => 'Dummy Role')
        );
        $this->assertEquals($this->roles->getData(), array('name'=>'Dummy Role'));
        $this->roles->setData(
            array('id' => '2')
        );
        $this->assertEquals($this->roles->getData(),
            array(
                'id' => '2',
                'name' => 'Dummy Role'
            )
        );

        $this->roles->setData(
            array('name' => 'Dummiest Role'),
            true
        );
        
        $this->assertEquals($this->roles->getData(),
            array(
                'name' => 'Dummiest Role'
            )
        );
    }

    public function testSave()
    {
        $this->roles->name = "Added Role";
        $this->roles->save();
    }
    
    public function testUpdate()
    {
    	
    }
    
    public function testDelete()
    {
    	
    }
}
