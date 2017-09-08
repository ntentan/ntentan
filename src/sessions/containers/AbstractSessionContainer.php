<?php

namespace ntentan\sessions\containers;

abstract class AbstractSessionContainer implements \SessionHandlerInterface
{
    abstract public function open($sessionPath, $sessionName);

    abstract public function close();

    abstract public function read($sessionId);

    abstract public function write($sessionId, $data);

    abstract public function destroy($sessionId);

    abstract public function gc($lifetime);

    abstract public function isNew();

}
