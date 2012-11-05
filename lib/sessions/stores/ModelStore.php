<?php
namespace ntentan\sessions\stores;

use ntentan\sessions\Manager;
use ntentan\Ntentan;
use ntentan\models\Model;

require_once "Store.php";

class ModelStore implements Store
{
    private $session;
    private $new = false;
    
    public function open($sessionName, $sessionId)
    {
        $this->session = Model::load('sessions');
    }

    public function close()
    {
        return true;
    }
    
    public function read($sessionId)
    {
        $this->session = $this->session->getFirstWithId($sessionId);
        if($this->session->count() == 1)
        {
            return $this->session->data;
        }
        else
        {
            $this->new = true;
            return '';
        }
    }
    
    public function write($sessionId, $sessionData)
    {
        if($this->new)
        {
            $this->session->id = $sessionId;
            $this->session->data = $sessionData;
            $this->session->expires = time() + Manager::$lifespan;
            $this->session->lifespan = Manager::$lifespan;
            $this->session->save();
        }
        else
        {
            $this->session->data = $sessionData;
            $this->session->expires = time() + Manager::$lifespan;
            $this->session->lifespan = Manager::$lifespan;
            $this->session->update();
        }
    }
    
    public function destroy($sessionId)
    {
        $this->session->delete();
    }
    
    public function gc($lifetime)
    {
        
    }
    
    public function isNew()
    {
        return $this->new;
    }
}
