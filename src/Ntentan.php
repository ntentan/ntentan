<?php
/**
 * Common utilities file for the Ntentan framework. This file contains a
 * collection of utility static methods which are used accross the framework.
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

use ntentan\utils\Text;
use ntentan\config\Config;

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
class Ntentan
{
    /**
     * Root namespace for entire application.
     *
     * @var string
     */
    private static $namespace;

    /**
     *
     *
     * @var string
     */
    private static $configPath = 'config/';


    private static $prefix;
    
    public static function init($namespace)
    {
        self::$namespace = $namespace;
        self::$prefix = Config::get('app.prefix');
        self::$prefix = (self::$prefix == '' ? '' : '/') . self::$prefix;
        
        self::setupAutoloader();

        logger\Logger::init('logs/app.log');

        Config::readPath(self::$configPath, 'ntentan');
        kaikai\Cache::init();
        
        panie\InjectionContainer::bind(nibii\interfaces\ClassResolverInterface::class)
            ->to(ClassNameResolver::class);
        panie\InjectionContainer::bind(nibii\interfaces\ModelJoinerInterface::class)
            ->to(ClassNameResolver::class);
        panie\InjectionContainer::bind(nibii\interfaces\TableNameResolverInterface::class)
            ->to(nibii\ClassNameResolver::class);
        panie\InjectionContainer::bind(panie\ComponentResolverInterface::class)
            ->to(ClassNameResolver::class);
        panie\InjectionContainer::bind(controllers\interfaces\ClassResolverInterface::class)
            ->to(ClassNameResolver::class);
        
        if(Config::get('ntentan:db.driver')){
            panie\InjectionContainer::bind(nibii\DriverAdapter::class)
                ->to(nibii\ClassNameResolver::getDriverAdapterClassName());
            panie\InjectionContainer::bind(atiaa\Driver::class)
                ->to(atiaa\Db::getDefaultDriverClassName());
        }
        
        Controller::setComponentResolverParameters([
            'type' => 'component',
            'namespaces' => [$namespace, 'controllers\components']
        ]);
        nibii\RecordWrapper::setComponentResolverParameters([
            'type' => 'behaviour',
            'namespaces' => [$namespace, 'nibii\behaviours']
        ]);    
    }
    
    private static function setupAutoloader()
    {
        spl_autoload_register(function ($class) {
            $prefix = Ntentan::getNamespace() . "\\";
            $baseDir = 'src/';
            $len = strlen($prefix);

            if (strncmp($prefix, $class, $len) !== 0) {
                return;
            }

            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });          
    }
    
    public static function run()
    {
        Session::start();
        honam\TemplateEngine::prependPath('views/shared');
        honam\TemplateEngine::prependPath('views/layouts');
        honam\AssetsLoader::setSiteUrl(self::getUrl('public'));
        honam\AssetsLoader::appendSourceDir('assets');
        honam\AssetsLoader::setDestinationDir('public');     
        Router::execute(substr(utils\Input::server('REQUEST_URI'), 1));        
    }

    public static function getNamespace()
    {
        return self::$namespace;
    }

    public static function getUrl($url)
    {
        $prefix = Config::get('app.prefix');
        $newUrl = ($prefix == '' ? '' : '/') . $prefix;
        if($url) {
            $newUrl .= ($url[0] == '/' ? '' : '/') . "$url";
        }
        return $newUrl;
    }
}
