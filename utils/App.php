<?php
/* 
 * Ntentan PHP Framework
 * Copyright 2010 James Ekow Abaka Ainooson
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ntentan\utils;

use ntentan\Ntentan;

/**
 * Utility script class for interacting with applications. This script can
 * setup application directories as well as modify existing ones.
 */
class App extends Util
{
    protected $shortOptionsMap = array(
        "i" => "interactive"
    );

    /**
     * This method adds models, controllers or views to the application
     * @param array $options
     */
    public function add($options)
    {
        switch($options[0])
        {
        case 'model':
            require 'config/ntentan.php';
            
            break;
        case 'controller':
            break;
        }
    }

    /**
     * This method creates a new application by setting up the directories
     * and files. It also creates all the initial necessary bootstrap codes
     * needed for the application to work properly.
     *
     * @todo create a default home page for the application
     * @todo create the resource, layouts directories and the public directories
     * @todo the default home page should check for all permissions which are
     *       necessary
     * @param array $options
     */
    public function create($options)
    {
        // Extract the parameters for the options
        if($options["interactive"])
        {
            echo "Ntentan PHP Framework\nVersion " . Ntentan::VERSION . "\n\n";
            echo "Creating a new application\n";
            echo "Enter the application name: ";
            $name = trim(fgets(STDIN));
            echo "Enter the module name for application: ";
            $module = trim(fgets(STDIN));
            echo "\n";
        }
        else
        {
            if($options["name"] != "")
            {
                $name = $options["name"];
            }
            else
            {
                fputs(STDERR, "Option --name not found.\n");
                die();
            }

            if($options["module"] != "")
            {
                $module = $options["module"];
            }
            else
            {
                fputs(STDERR, "Option --module not found.\n");
                die();
            }
        }
        
        // Generate the index file
        echo "Generating index.php ...\n";
        file_put_contents(
            "index.php",
            file_get_contents(NTENTAN_HOME . "utils/files/_index.php")
        );
        
        echo "Copying .htaccess ...\n";
        file_put_contents(
            ".htaccess", 
            file_get_contents(NTENTAN_HOME . "utils/files/_htaccess")
        );
        
        echo "Creating config directory ...\n";
        mkdir("config");
        mkdir($module);
        file_put_contents(
            "config/ntentan.php",
            str_replace(
                array(
                    "{ntentan_home}",
                    "{module}"
                ),
                array(
                    NTENTAN_HOME,
                    $module
                ),
                file_get_contents(NTENTAN_HOME . "utils/files/_ntentan.php")
            )
        );

        echo "Creating home controller ...\n";
        mkdir("$module/home");
    }
}
