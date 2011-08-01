<?php
namespace tests\mocks\modules\departments;

class Departments extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );
}