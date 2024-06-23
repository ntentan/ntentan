<?php 
/**
 * Root namespace for all ntentan classes
 * @author ekow
 */

namespace ntentan;

use ntentan\config\Config;

/**
 * A context within which the current request is served.
 * The context holds instances of utility classes that are needed by ntentan in order to serve a request.
 *
 * @author     James Ainooson <jainooson@gmail.com>
 * @license    MIT
 */
readonly class Context
{

    public function __construct(
        private string $namespace,
        private array $config
    ) { }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    // /**
    //  * An instance of the \ntentan\config\Config object which holds the applications configurations.
    //  *
    //  * @var \ntentan\config\Config
    //  */
    // private array $config;

    // /**
    //  * The namespace under which the application code is kept.
    //  *
    //  * @var string
    //  */
    // private string $namespace = 'app';

    // /**
    //  * Stores parameters that are shared across the application.
    //  *
    //  * @var array
    //  */
    // private $parameters = [];

    // private $prefix;

    // /**
    //  * Constructor for the context
    //  *
    //  * @param string $namespace
    //  */
    // public function __construct(string $namespace, array $config)
    // {
    //     $this->namespace = $namespace;
    //     $this->config = $config;
    // }

    // /**
    //  * Get the namespace for this application.
    //  *
    //  * @return string
    //  */
    // public function getNamespace()
    // {
    //     return $this->namespace;
    // }

    // // public function getUrl($path)
    // // {
    // //     return preg_replace('~/+~', '/', $this->prefix . "/$path");
    // // }

    // // public function getParameter($parameter)
    // // {
    // //     return $this->parameters[$parameter] ?? '';
    // // }

    // // public function setParameter($parameter, $value)
    // // {
    // //     $this->parameters[$parameter] = $value;
    // // }

    // // public function getPrefix()
    // // {
    // //     return $this->prefix;
    // // }

    // public function getConfig()
    // {
    //     return $this->config;
    // }
}

