<?php
/**
 * Created by PhpStorm.
 * User: ekow
 * Date: 9/3/17
 * Time: 8:34 AM
 */

namespace ntentan\middleware\mvc;


use ntentan\exceptions\RouteNotAvailableException;

class ResourceLoaderFactory
{
    private $loaders = [];

    public function registerLoader($key, $class)
    {
        $this->loaders[$key] = $class;
    }

    public function createLoader($parameters)
    {
        foreach ($this->loaders as $key => $class) {
            if (isset($parameters[$key])) {
                $loader = new $class();
                return $loader->load($parameters);
            }
        }
        throw new \Exception("Failed to find a suitable loader");
    }
}
