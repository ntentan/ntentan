<?php
namespace tests\modules\departments;

class Departments extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );
}