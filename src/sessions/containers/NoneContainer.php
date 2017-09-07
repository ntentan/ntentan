<?php

namespace ntentan\sessions\containers;

use ntentan\sessions\SessionContainer;

class NoneContainer extends SessionContainer
{

    private $file;
    private $sessionName;
    private $sessionPath;

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
    }

    public function write($sessionId, $data)
    {
        return true;
    }

    public function destroy($sessionId)
    {
    }

    public function gc($lifetime)
    {
        return true;
    }

    public function isNew()
    {
        return true;
    }

}
