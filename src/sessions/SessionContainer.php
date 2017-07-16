<?php

namespace ntentan\sessions;

use ntentan\Context;
use ntentan\utils\Text;

abstract class SessionContainer implements \SessionHandlerInterface
{

    protected $lifespan;
    protected $context;

    public function __construct(Context $context)
    {
        $this->lifespan = $context->getConfig()->get('app.sessions.lifespan', 86000);
        $this->context = $context;
        session_set_save_handler($this, true);
    }

    abstract public function open($sessionPath, $sessionName);

    abstract public function close();

    abstract public function read($sessionId);

    abstract public function write($sessionId, $data);

    abstract public function destroy($sessionId);

    abstract public function gc($lifetime);

    abstract public function isNew();

    public static function getClassName($container)
    {
        return '\ntentan\sessions\containers\\' . Text::ucamelize($container) . 'Container';
    }

}
