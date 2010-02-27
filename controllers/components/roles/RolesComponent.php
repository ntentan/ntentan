<?php
class RolesComponent extends Component
{
    public function preRender()
    {
        $redirect = true;
        foreach($this->excludedPaths as $excludedPath)
        {
            if(Ntentan::$route == $excludedPath)
            {
                $redirect = false;
            }
        }

        if($_SESSION["ntentan_logged_in"] == false &&
            Ntentan::$route != $this->loginPath &&
            Ntentan::$route != $this->logoutPath &&
            $redirect
        )
        {
            Ntentan::redirect($this->loginPath . "?redirect=" . urlencode(Ntentan::getRequestUri()));
        }
    }
}
