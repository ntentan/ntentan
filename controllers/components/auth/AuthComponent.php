<?php
/**
 * 
 */
class AuthComponent extends Component
{
    public $loginPath = "users/login";
    public $logoutPath = "users/logout";
    public $redirectPath = "/";
    //public $excludedPaths = array();
    public $name = __CLASS__;

    /*public function preRender()
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
    }*/

    public function login()
    {
        if(isset($_POST["username"]) && isset($_POST["password"]))
        {
            $users = Model::load("users");
            $result = $users->getFirstWithUsername($_POST["username"]);
            if($result->password == md5($_POST["password"]))
            {
                $_SESSION["logged_in"] = true;
                $_SESSION["username"] = $_POST["username"];
                $_SESSION["user_id"] = $result["id"];
                Ntentan::redirect($_GET["redirect"] == null ? $this->redirectPath : $_GET["redirect"], true);
            }
            else
            {
                $this->set("login_message", "Invalid username or password" );
            }
        }
    }

    public function logout()
    {
        $_SESSION = array();
        Ntentan::redirect($this->redirectPath);
    }
}