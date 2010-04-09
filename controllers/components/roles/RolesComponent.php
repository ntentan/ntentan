<?php
class RolesComponent extends Component
{
    public $anonymous;
    public $authenticated;

    public function preRender()
    {
        if($_SESSION["logged_in"] == false)
        {
            foreach($this->anonymous as $anonymous)
            {
                if(preg_match("/{$anonymous["path"]}/", Ntentan::$route) > 0)
                {
                    if($anonymous["access"] == "DISALLOW")
                    {
                        if(isset($anonymous["fallback"]))
                        {
                            Ntentan::redirect($anonymous["fallback"]);
                        }
                        else
                        {
                            $this->controller->authComponent->login();
                            header("HTTP/1.0 404 Not Found");
                            die();
                        }
                    }
                }
            }
        }
    }
}
