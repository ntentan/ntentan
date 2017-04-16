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

use ntentan\config\Config;
use ntentan\controllers\Url;
use ntentan\interfaces\ControllerClassResolverInterface;
use ntentan\nibii\interfaces\ModelClassResolverInterface;
use ntentan\nibii\interfaces\ModelJoinerInterface;
use ntentan\nibii\interfaces\TableNameResolverInterface;
use ntentan\panie\ComponentResolverInterface;
use ntentan\nibii\DriverAdapter;
use ntentan\nibii\Resolver;
use ntentan\utils\Input;

/**
 * Include a collection of utility global functions, caching and exceptions.
 * Classes loaded here are likely to be called before the autoloader kicks in.
 */

/**
 * A utility class for the Ntentan framework. This class contains the routing
 * framework used for routing the pages. Routing involves the analysis of the
 * URL and the loading of the controllers which are requested through the URL.
 * This class also has several utility methods which help in the overall
 * operation of the entire framework.
 *
 *  @author     James Ainooson <jainooson@gmail.com>
 *  @license    MIT
 */
class Context {

    /**
     * Directory where application configurations are stored.
     *
     * @var string
     */
    private $configPath = 'config/';

    /**
     * A prefix to expect in-front of all URLS. This is useful when running your
     * application through a sub-directory.
     * 
     * @var string
     */
    private $prefix;
    
    private $container;
    
    private $namespace = 'app';
    
    /**
     *
     * @var Application 
     */
    private $app;
    
    /**
     * @return Context New context
     */
    public static function initialize($namespace = 'app') {
        $container = new panie\Container();
        // Force binding of context as singleton in container
        $container->bind(self::class)->to(self::class)->asSingleton();
        return $container->resolve(
            self::class, ['container' => $container, 'namespace' => $namespace]
        );
    }

    /**
     * Initializes an application that has all its classes found in the base
     * namespace.
     * 
     * @param panie\Container $container
     * @param string $namespace
     */
    public function __construct($container, $namespace) {
        $this->container = $container;
        $this->namespace = $namespace;
        $this->setupAutoloader();
        $this->prefix = Config::get('app.prefix');
        $this->prefix = ($this->prefix == '' ? '' : '/') . $this->prefix;

        //self::setupAutoloader();

        logger\Logger::init('logs/app.log');

        Config::readPath($this->configPath, 'ntentan');
        kaikai\Cache::init();

        $container->bind(ModelClassResolverInterface::class)->to(ClassNameResolver::class);
        $container->bind(ModelJoinerInterface::class)->to(ClassNameResolver::class);
        $container->bind(TableNameResolverInterface::class)->to(nibii\Resolver::class);
        $container->bind(ComponentResolverInterface::class)->to(ClassNameResolver::class);
        $container->bind(ControllerClassResolverInterface::class)->to(ClassNameResolver::class);

        if (Config::get('ntentan:db.driver')) {
            $container->bind(DriverAdapter::class)->to(Resolver::getDriverAdapterClassName());
            $container->bind(atiaa\Driver::class)->to(atiaa\Db::getDefaultDriverClassName());
        }

        Controller::setComponentResolverParameters([
            'type' => 'component',
            'namespaces' => [$namespace, 'controllers\components']
        ]);
        nibii\RecordWrapper::setComponentResolverParameters([
            'type' => 'behaviour',
            'namespaces' => [$namespace, 'nibii\behaviours']
        ]);
        
        controllers\ModelBinderRegister::setDefaultBinderClass(
            controllers\model_binders\DefaultModelBinder::class
        );
        controllers\ModelBinderRegister::register(
            utils\filesystem\UploadedFile::class, controllers\model_binders\UploadedFileBinder::class
        );
    }

    /**
     * Initialises ntentan's autoloader mechanism for classes that require
     * the application's namespace. These would be the classes that you
     * would write for this application.
     */
    private function setupAutoloader() {
        spl_autoload_register(function ($class) {
            $prefix = $this->namespace . "\\";
            $baseDir = 'src/';
            $len = strlen($prefix);
            
            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                include $file;
            }
        });
    }
    
    public function getNamespace() {
        return $this->namespace;
    }
    
    /**
     * 
     * @return Router
     */
    public function getRouter() {
        return $this->container->singleton(Router::class);
    }
    
    public function getContainer() {
        return $this->container;
    }
    
    public function getApp() {
        return $this->app;
    }

    public function execute($applicationClass = Application::class) {
        Session::start();
        $this->app = $this->container->resolve($applicationClass);
        $this->app->setup();
        $route = $this->getRouter()->route(substr(Input::server('REQUEST_URI'), 1));
        $pipeline = $this->app->getPipeline();
        $this->container->resolve(PipelineRunner::class)->run($pipeline, $route);
    }

}
