<?php
/**
 * Source file for the data store class
 * 
 * Ntentan Framework
 * Copyright (c) 2010-2012 James Ekow Abaka Ainooson
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 * 
 * @category ORM
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright 2010-2012 James Ainooson
 * @license MIT
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
    public abstract function countAllItems();
}
