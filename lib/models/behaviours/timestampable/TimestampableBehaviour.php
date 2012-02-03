<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\models\behaviours\timestampable;

use ntentan\models\Behaviour;

class TimestampableBehaviour extends Behaviour
{
    private $hasCreated;
    private $hasUpdated;
    private $createdField = 'created';
    private $updatedField = 'updated';
    
    public function __construct()
    {
        
    }
    
    public function init($model)
    {
        $description = $model->describe();
        $fields = array_keys($description['fields']);
        if(array_search($this->createdField, $fields) !== false) $this->hasCreated = true;
        if(array_search($this->updatedField, $fields) !== false) $this->hasUpdated = true;
    }
    
    public function preSave(&$data)
    {
        if($this->hasCreated)
        {
            $data[$this->createdField] = date("Y-m-d H:i:s");
        }
    }
    
    public function preUpdate(&$data)
    {
        if($this->hasUpdated)
        {
            $data[$this->updatedField] = date("Y-m-d H:i:s");
        }
    }
}
