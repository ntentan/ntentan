<?php

namespace ntentan\utils;

use ntentan\Ntentan;

class Create extends Util
{
    protected $shortOptionsMap = array(
        "i" => "interactive"
    );

    private function mkdir($name)
    {
        $basePath = str_replace('.', '/', $this->module);
        $path = $basePath . "/$name";
        echo "Creating directory $path\n";
        if(!\is_writable($basePath))
        {
            echo "You do not have permissions to created this directory\n";
            die();
        }
        if(\is_dir($path))
        {
            echo "Directory $name already exists. Skipping creation ...\n";
        }
        else
        {
            mkdir($path);
        }
        return $path;
    }

    public function model($options)
    {
        if(is_string($options))
        {
            $name = $options;
        }
        else
        {
            $name = $options['stand_alone_values'][0];
        }

        $directory = Create::mkdir($name);
        $modelClassName = Ntentan::camelize($name);
        $table = end(explode(".", $name));

        echo "Creating model class file $directory/$modelClassName.php\n";

        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/schema_templates/_Model.php',
            "$directory/$modelClassName.php",
            array(
                'module' => $this->module,
                'table_name' => $table,
                'has_many' => null,
                'belongs_to' => null,
                'class_name' => $modelClassName
            )
        );

        echo "Done!\n";
    }
    
    /**
     * 
     * @param array $options
     */
    public function controller($options)
    {
        if(is_string($options))
        {
            $name = $options;
        }
        else
        {
            $name = $options['stand_alone_values'][0];
        }

        $directory = Create::mkdir($name);
        $className = Ntentan::camelize($name) . 'Controller';
        $this->templateCopy(
            NTENTAN_HOME . "utils/files/create_templates/_Controller.php",
            "$directory/$className.php",
            array(
                'module' => $this->module,
                'name' => $name,
                'class_name' => $className
            )
        );
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
            file_get_contents(NTENTAN_HOME . "utils/files/create_templates/_index.php")
        );

        echo "Copying .htaccess ...\n";
        $this->templateCopy(
            NTENTAN_HOME . "utils/files/create_templates/_htaccess",
            '.htaccess'
        );

        echo "Creating config directory ...\n";
        mkdir("config");
        mkdir($module);
        $this->module = $module;

        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/create_templates/_ntentan.php',
            'config/ntentan.php',
            array(
                'ntentan_home' => NTENTAN_HOME,
                'module' => $module
            )
        );

        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/create_templates/_app.php',
            'config/app.php',
            array(
                'app_name' => $name
            )
        );

        echo "Creating public directory";
        mkdir('public');

        echo "Creating cache directory";
        mkdir('cache');

        echo "Creating home controller ...\n";
        Create::controllerFiles('home');
        $this->templateCopy(
            NTENTAN_HOME . 'utils/files/create_templates/_home_run.tpl.php',
            "$module/home/run.tpl.php"
        );
    }
}