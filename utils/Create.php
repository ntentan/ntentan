<?php

namespace ntentan\utils;

use ntentan\Ntentan;

class Create extends Util
{
    protected $shortOptionsMap = array(
        "i" => "interactive"
    );
    
    /**
     * 
     * @param array $name
     */
    private function controllerDirectory($name)
    {
        $directory = str_replace('.', '/', $this->module) . "/$name";
        mkdir($directory);
        $className = Ntentan::camelize($name) . 'Controller';
        $this->templateCopy(
            NTENTAN_HOME . "utils/files/new_templates/_Controller.php",
            "$directory/$className.php",
            array(
                'module' => $this->module,
                'name' => $name,
                'class_name' => $className
            )
        );
    }

    /**
     * 
     * @param array $options
     */
    public function controller($options)
    {
        if(is_array($options))
        {

        }
        else if(is_string($options))
        {
            Create::_controllerCore($options);
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
    public function app($options)
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
            file_get_contents(NTENTAN_HOME . "utils/files/new_templates/_index.php")
        );

        echo "Copying .htaccess ...\n";
        $this->templateCopy(
            NTENTAN_HOME . "utils/files/new_templates/_htaccess", 
            '.htaccess'
        );

        echo "Creating config directory ...\n";
        mkdir("config");
        mkdir($module);
        $this->module = $module;

        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/new_templates/_ntentan.php',
            'config/ntentan.php',
            array(
                'ntentan_home' => NTENTAN_HOME,
                'module' => $module
            )
        );

        echo "Creating home controller ...\n";
        Create::controllerDirectory('home');
        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/new_templates/_home_run.tpl.php',
            "$module/home/run.tpl.php"
        );
    }
}