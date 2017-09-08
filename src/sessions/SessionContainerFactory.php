<?php
/**
 * Created by PhpStorm.
 * User: ekow
 * Date: 9/7/17
 * Time: 8:42 AM
 */

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

    public function createSessionContainer()
    {
        $sessionContainerType = $this->config['container'] ?? 'default';
        $className = '\ntentan\sessions\containers\\' . Text::ucamelize($sessionContainerType) . 'SessionContainer';
        $sessionContainer = new $className($this->config);
        return $sessionContainer;
    }
}