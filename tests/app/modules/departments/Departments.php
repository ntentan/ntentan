<?php
namespace ntentan_test_app\modules\departments;

class Departments extends \ntentan\models\Model
{
    public $hasMany = array(
        'users'
    );
}