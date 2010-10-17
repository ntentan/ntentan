<?php
namespace ntentan\utils;

use ntentan\Ntentan;

class App extends Util
{
    protected $shortOptionsMap = array(
        "i" => "interactive"
    );
    
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
    }
}
