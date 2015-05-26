<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ntentan\models\datastores;

/**
 * Description of Sqlite
 *
 * @author ekow
 */
class Sqlite extends Atiaa
{
    protected function fixType($type) 
    {
        switch($type)
        {
            case 'text': return 'string';
            default: return $type;
        }
    }

    protected function limit($limitParams) 
    {
        return (isset($limitParams['limit']) ? " LIMIT {$limitParams['limit']}":'') .
               (isset($limitParams['offset']) ? " OFFSET {$limitParams['offset']}":'');        
    }
    
    public function __toString()
    {
        return 'sqlite';
    }
}
