<?php
namespace ntentan\sessions\stores;

use ntentan\sessions\Manager;

require_once "Store.php";

class FileStore implements Store
{
    private $file;
    
    public function open($sessionPath, $sessionName)
    {
        return true;
    }
    
    public function close()
    {
        return true;
    }
    
    public function read($sessionId)
    {
        $this->file = getcwd() . "/tmp/nt_sess_$sessionId";
        if (file_exists($this->file)) {
            if (filemtime($this->file) + Manager::$expiry > time()) {
                return file_get_contents($this->file);
            } else {
                return '';
            }
        } else {
            return '';
        }
    }
    
    public function write($sessionId, $data)
    {
        $file = fopen($this->file, "w");
        
        if ($file !== false) {
            fwrite($file, $data);
            fclose($file);
            return true;
        } else {
            return false;
        }
    }
    
    public function destroy($sessionId)
    {
        $file = $this->getSessionFile($sessionId);
        return unlink($file);
    }
    
    public function gc($lifetime)
    {
        foreach (glob("{$this->path}/sess_*") as $filename) {
            if (filemtime($filename) + Manager::$expiry < time()) {
                unlink($file);
            }
        }
        return true;
    }
    
    public function isNew()
    {
        return true;
    }
}
