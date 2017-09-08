<?php

namespace ntentan\sessions\containers;

class NoneSessionContainer extends AbstractSessionContainer
{
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
