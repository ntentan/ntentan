<?php

namespace ntentan\sessions;

use ntentan\utils\Text;
use ntentan\config\Config;

class SessionContainerFactory
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config->get('app.sessions');
    }

    /**
     * Create and return an instance of the custom session container if required.
     */
    public function createSessionContainer()
    {
        // Check and start a default session if a custom session container is not supplied
        if(!isset($this->config['container'])) {
            session_start();
            return;
        }
        // Create an instance of the session container. Session containers are responsible for starting the session.
        $sessionContainerType = $this->config['container'];
        $className = '\ntentan\sessions\containers\\' . Text::ucamelize($sessionContainerType) . 'SessionContainer';
        $sessionContainer = new $className($this->config);
        return $sessionContainer;
    }
}