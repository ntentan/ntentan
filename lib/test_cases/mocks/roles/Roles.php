<?php
namespace lib\test_cases\mocks\roles;

class Roles extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );

    public $mustBeUnique = array(
        array('field' => 'name', 'message' => 'Two roles cannot have the same name')
    );
}