<?php
/*
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
 *
 */

namespace ntentan\controllers\components\roles;
use ntentan\Ntentan;
use \ntentan\controllers\components\Component;

class RolesComponent extends Component
{
    public $anonymous = array();
    public $authenticated = array();

    public function preRender()
    {
        if($_SESSION["logged_in"] === true)
        {
            if(is_array($this->authenticated[$_SESSION['role_id']]))
            {
                foreach($this->authenticated[$_SESSION['role_id']] as $authenticated)
                {
                    if(is_string($authenticated))
                    {
                        if(preg_match($authenticated, Ntentan::$route, $members))
                        {
                            return;
                        }
                    }
                }
                header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
                die();
            }
        }
        else
        {
            foreach($this->anonymous as $anonymous)
            {
                if(Ntentan::$route === $this->controller->authComponent->loginRoute)
                {
                    $this->controller->authComponent->login();
                    break;
                }
                else if(preg_match("{$anonymous["path"]}", Ntentan::$route) > 0)
                {
                    if($anonymous["access"] == "disallow")
                    {
                        if(isset($anonymous["fallback"]))
                        {
                            Ntentan::redirect($anonymous["fallback"]);
                        }
                        else
                        {
                            Ntentan::redirect($this->controller->authComponent->loginRoute . "&redirect=" . \urlencode(Ntentan::$route));
                        }
                    }
                }
            }
        }
    }
}
