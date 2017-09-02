<?php

namespace ntentan\sessions;

use ntentan\config\Config;

abstract class SessionContainer implements \SessionHandlerInterface
{

    protected $lifespan;
    protected $config;

    public function __construct(Config $config)
    {
        $this->lifespan = $config->get('app.sessions.lifespan', 86000);
        $this->config = $config;
        session_set_save_handler($this, true);
    }

    abstract public function open($sessionPath, $sessionName);

    abstract public function close();

    abstract public function read($sessionId);

    abstract public function write($sessionId, $data);

    abstract public function destroy($sessionId);

    abstract public function gc($lifetime);

    abstract public function isNew();

}
