<?php

/**
 * Common utilities file for the Ntentan framework. This file contains a
 * collection of utility methods which are used accross the framework.
 *
 * Ntentan Framework
 * Copyright (c) 2008-2015 James Ekow Abaka Ainooson
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author James Ainooson <jainooson@gmail.com>
 * @copyright Copyright 2010 James Ekow Abaka Ainooson
 * @license MIT
 */
/**
 * Root namespace for all ntentan classes
 * @author ekow
 */

namespace ntentan;

/**
 * A context within which the current request is served.
 * The context holds instances of utility classes that are needed by ntentan in order to serve a request.
 *
 * @author     James Ainooson <jainooson@gmail.com>
 * @license    MIT
 */
class Context
{

    /**
     * An instance of the \ntentan\config\Config object which holds the applications configurations.
     *
     * @var \ntentan\config\Config
     */
    private $config;
    
    /**
     * The namespace under which the application code is kept.
     * 
     * @var string
     */
    private $namespace = 'app';
    
    /**
     * An instance of the caching class.
     * 
     * @var \ntentan\kaikai\Cache
     */
    private $cache;
    
    /**
     * Stores parameters that are shared across the application.
     * 
     * @var \ntentan\Parameters
     */
    private $parameters;
    
    /**
     * A static instance of this context. 
     * Used in situations where the context is accessed statically.
     * 
     * @var ntentan\Context
     */
    private static $instance;

    private $modelBinderRegistry;
    
    private $prefix;

    /**
     * Create an instance of the context.
     * 
     * @param string $namespace The namespace for the application
     * @param string $applicationClass The name of an application class to be setup with the context.
     * @param \ntentan\panie\Container $container A dependency injection container to use.
     * 
     * @return Context New context
     */
    public static function initialize($namespace, $config, $cache)
    {
        $context = new self($namespace);
        $context->cache = $cache;
        $context->config = $config;
        $context->prefix = $config->get('app.prefix', null);
        $context->parameters = Parameters::wrap([]);
        self::$instance = $context;
        return $context;
    }
    
    /**
     * Return the static instance of the context created during initialization.
     * 
     * @return Context
     */
    public static function getInstance()
    {
        if(self::$instance === null) {
            throw new exceptions\NtentanException("You have not initialized the ntentan context.");
        }
        return self::$instance;
    }

    /**
     * Constructor for the context
     *
     * @param panie\Container $container
     * @param string $namespace
     */
    private function __construct($namespace)
    {
        $this->namespace = $namespace;
    }


    /**
     * Get the namespace for this application.
     * 
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Get an instance of the cache.
     * 
     * @return kaikai\Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getRedirect($path)
    {
        return new Redirect($path);
    }

    public function getUrl($path)
    {
        return preg_replace('~/+~', '/', $this->prefix . "/$path");
    }
    
    /**
     * @return controllers\ModelBinderRegistry
     */
    public function getModelBinderRegistry()
    {
        return $this->modelBinderRegistry;
    }

    public function setModelBinderRegistry($modelBinderRegistry)
    {
        $this->modelBinderRegistry = $modelBinderRegistry;
    }

    public function getParameter($parameter)
    {
        return $this->parameters->get($parameter);
    }
    
    public function setParameter($parameter, $value)
    {
        $this->parameters->set($parameter, $value);
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function getConfig()
    {
        return $this->config;
    }
}
