<?php
namespace lib\test_cases\mocks\modules\departments;

class Departments extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );
}