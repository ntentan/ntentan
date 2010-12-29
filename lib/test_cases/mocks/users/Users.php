<?php
namespace lib\test_cases\mocks\users;

class Users extends \ntentan\models\Model
{
    public $belongsTo = array(
        'role',
        array('department', 'as' => 'office')
    );

    public $mustBeUnique = array(
        'username'
    );
}