<?php
namespace lib\test_cases\mocks\departments;

class Departments extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );
}