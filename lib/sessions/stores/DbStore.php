<?php
namespace ntentan\sessions\stores;

use ntentan\sessions\Manager;
use ntentan\models\datastores\Mysql;
use ntentan\Ntentan;

require_once "Store.php";

class DbStore implements Store
{
    private $db;
    
    public function open($sessionPath, $sessionName)
    {
        $this->db = Ntentan::getDefaultDataStore(true);
    }
    
    public function write($sessionId, $data)
    {
        $this->db->query("SELECT FROM sessions WHERE id = '%s' AND expires > %d", $sessionId, time())
        return true;
    }
    
    public function read($sessionId)
    {
        $this->db->query("SELECT FROM sessions WHERE id = '%s' AND expires > %d", $sessionId, time());
    }
    
    public function close()
    {
        return true;
    }
    
    public function destroy($sessionId)
    {
        
    }
    
    public function gc($lifetime)
    {
        
    }
}
