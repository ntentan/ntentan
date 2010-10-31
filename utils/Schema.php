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
use ntentan\models\Model;

class Schema extends Util
{
    private function getDatastore()
    {
        if(!file_exists("config/db.php"))
        {
            $datastoreParams = $this->createNewDatastoreConfig();
        }
        $datastoreParams = Ntentan::getDefaultDatastore();
        $datastoreName = Ntentan::camelize($datastoreParams["datastore"]);
        $datastoreClass = "ntentan\\models\\datastores\\$datastoreName";
        return new $datastoreClass($datastoreParams);
    }
    
    private function createNewDatastoreConfig()
    {
        echo "Oops! There are no schema configuration files.\n";
        $create = $this->getUserResponse(
            "Should a schema configuration be created",
            array(
                "y", "n"
            ),
            "y"
        );
        if($create == "y")
        {
            $backend = $this->getUserResponse(
                "Which datastore backend should be used",
                array(
                    "mysql", "pgsql"
                ),
                "mysql"
            );
            
            $host = $this->getUserResponse(
                "What is the host of the datastore's database",
                null,
                "localhost"
            );
            
            $username = $this->getUserResponse(
                "What is the username to use when connecting to the datastore's database",
                null,
                "",
                true
            );
            
            $password = $this->getUserResponse(
                "What is the password of the above selected username",
                null,
                ""
            );
            
            $defaultSchema = $this->getUserResponse(
                "What is the default database schema for this application",
                null,
                ""
            );
            file_put_contents(
                "config/db.php",
                file_get_contents(Ntentan::getFilePath("utils/files/db.php.template")) .
                "\$datastores = array("
                . "    'default' => array("
                . "        'datastore' => '$backend',"
                . "        'host'      => '$host',"
                . "        'username'  => '$username',"
                . "        'password'  => '$password',"
                . "        'database'  => '$defaultSchema'"
                . "    )"
                . ");"
            );
        }
        else
        {
            die("Aborting schema import.");
        }
    }
    
    public function import($options)
    {
        require "config/ntentan.php";
        echo "Extracting schema information from database ... \n";
        $model = $this->getDatastore()->describeModel();
        foreach($model["tables"] as $table => $properties)
        {
            echo "Generating model class for $table table\n";
            
            // Get class name
            @mkdir("$modules_path/$table", 0755, true);
            $modelClassName = Ntentan::camelize($table) . "Model";
            $belongsToProperty = "";
            $hasManyProperty = "";
            
            // Get belongs to property
            if(count($properties["belongs_to"]) > 0)
            {
                $belongsToProperty = "    public \$belongsTo = array(\n";
                foreach($properties["belongs_to"] as $belongsTo)
                {
                    $belongsToProperty .= "        '$belongsTo',\n";
                }
                $belongsToProperty .= "    );\n";
            }
            
            if(count($properties["has_many"]) > 0)
            {
                $hasManyProperty = "    public \$hasMany = array(\n";
                foreach($properties["has_many"] as $hasMany)
                {
                    $hasManyProperty .= "        '$hasMany',\n";
                }
                $hasManyProperty .= "    );\n";
            }
            
            file_put_contents(
                "$modules_path/$table/$modelClassName.php",
                $this->substitute(
                    array(
                        "module" => $modules_path,
                        "table_name" => $table,
                        "has_many" => $hasManyProperty,
                        "belongs_to" => $belongsToProperty,
                        "class_name" => $modelClassName
                    ),
                    file_get_contents(Ntentan::getFilePath("utils/files/model.php.template"))
                )
            );
        }
    }

}