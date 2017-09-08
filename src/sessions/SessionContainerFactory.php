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
use ntentan\Session;

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
        $sessionContainer = new $className();
        $sessionContainer->setConfig($this->config);
        //Session::start();
        return $sessionContainer;
    }
}