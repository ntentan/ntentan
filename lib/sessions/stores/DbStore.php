<?php
namespace ntentan\sessions\stores;

use ntentan\sessions\Manager;
use ntentan\Ntentan;

require_once "Store.php";
/**
 * DBS
 * 
 * @author ekow
 */
class DbStore implements Store
{
    private $db;
    private $new = false;
    private $lifeSpan = 0;
    private $id;
    
    public static $saveJson = false;
    
    public function open($sessionPath, $sessionName)
    {
        $this->db = Ntentan::getDefaultDataStore(true);
    }
    
    public function write($sessionId, $data)
    {
        if($this->new)
        {
            $this->db->query(
                sprintf(
                    "INSERT into sessions(id, data, expires, lifespan) VALUES('%s', '%s', %d, %d)",
                    $sessionId, 
                    $this->db->escape($data), 
                    time() + Manager::$lifespan, 
                    Manager::$lifespan
                )
            );
        }
        else
        {
            
            $this->db->query(
                sprintf(
                    "UPDATE sessions SET data = '%s', expires = %d WHERE id = '%s'",
                    $this->db->escape($data), time() + Manager::$lifespan, $sessionId
                )
            );
        }
        return true;
    }
    
    public function read($sessionId)
    {
        $this->id = $sessionId;
        $result = $this->db->query(
            sprintf("SELECT data, lifespan FROM sessions WHERE id = '%s' AND expires > %d", $sessionId, time())
        );
        if(count($result) == 0)
        {
            $this->new = true;
            return '';
        }
        else
        {
            $this->lifeSpan = $result[0]['lifespan'];
            return $result[0]['data'];
        }
    }
    
    public function close()
    {
        return true;
    }
    
    public function destroy($sessionId)
    {
        $this->db->query(sprintf("DELETE FROM sessions WHERE id = '%s'", $sessionId));
        return true;        
    }
    
    public function gc($lifetime)
    {
        $this->db->query(sprintf("DELETE FROM sessions WHERE expiry < %d", time()));
        return true;
    }
    
    public function isNew()
    {
        return $this->new;
    }
}
