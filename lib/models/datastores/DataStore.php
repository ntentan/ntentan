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

namespace ntentan\models\datastores;

/**
 * 
 */
abstract class DataStore
{
    /**
     * The instance of the model utilizing this datastore.
     * @var Model
     */
    protected $model;
    
    /**
     * Put the datastore in debug mode
     * @var boolean
     */
    public $debug = false;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function get($queryParameters)
    {
        $return = null;
        if($queryParameters['type']=='count')
        {
            $return = (int)$this->_get($queryParameters);
        }
        else
        {
            $return = clone $this->model;
            $return->setData($this->_get($queryParameters), true);
            $return->count = $this->numRows();
        }
        return $return;
    }

    public function put()
    {
        $data = $this->model->getData();
        return $this->_put($data);
    }

    public function update()
    {
        $data = $this->model->getData();
        $this->_update($data);
    }

    public function delete()
    {
        $data = $this->model->getData();
        if($this->model->hasSingleRecord)
        {
            $this->_delete($data["id"]);
        }
        else
        {
            $toBeDeleted = array();
            foreach($data as $datum)
            {
                $toBeDeleted[] = $datum['id'];
            }
            $this->_delete($toBeDeleted);
        }
    }
    
    public function begin()
    {
        
    }
    
    public function end()
    {
        
    }

    protected abstract function _get($queryParameters);
    protected abstract function _put($queryParameters);
    protected abstract function _update($queryParameters);
    protected abstract function _delete($queryParameters);
    
    protected abstract function numRows();    
    public abstract function describe();
}
