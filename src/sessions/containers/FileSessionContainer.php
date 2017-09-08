<?php

namespace ntentan\sessions\containers;

class FileSessionContainer extends AbstractSessionContainer
{

    private $file;
    private $sessionName;
    private $sessionPath;
    private $config;
    private $lifespan;

    public function __construct($config)
    {
        $this->config = $config;
        $this->lifespan = $config['lifespan'] ?? 3600;
        session_set_save_handler($this, true);
        session_start();
    }

    public function open($sessionPath, $sessionName)
    {
        $this->sessionPath = $this->config['path'] ?? $sessionPath;
        $this->sessionName = $sessionName;
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $this->file = "{$this->sessionPath}/session_{$this->sessionName}_{$sessionId}";
        if (file_exists($this->file)) {
            if (filemtime($this->file) + $this->lifespan > time()) {
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
            if (filemtime($filename) + $this->lifespan < time()) {
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
