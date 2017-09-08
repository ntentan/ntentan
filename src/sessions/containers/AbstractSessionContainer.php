<?php

namespace ntentan\sessions\containers;

abstract class AbstractSessionContainer implements \SessionHandlerInterface
{
    protected $config;
    protected $lifespan;

    public function setConfig($config)
    {
        $this->config = $config;
        $this->lifespan = $config['lifespan'] ?? 3600;
    }

    abstract public function open($sessionPath, $sessionName);

    abstract public function close();

    abstract public function read($sessionId);

    abstract public function write($sessionId, $data);

    abstract public function destroy($sessionId);

    abstract public function gc($lifetime);

    abstract public function isNew();

}
