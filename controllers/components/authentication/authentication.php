<?php
/**
 * 
 */
class authentication extends AbstractComponent
{
    public $loginPath = "users/login";
    public $logoutPath = "users/logout";
    public $redirectPath;
    public $successUrl = "";
    public $name = __CLASS__;

    public function preRender()
    {
        if($_SESSION["ntentan_logged_in"] == false && 
            Ntentan::$route != $this->loginPath &&
            Ntentan::$route != $this->logoutPath)
        {
            Ntentan::redirect($this->loginPath . "?redirect=" . urlencode(Ntentan::getRequestUri()));
        }
    }

    public function login()
    {
        if(isset($_POST["username"]) && isset($_POST["password"]))
        {
            $users = Model::load("users");
            $result = $users->getWithUsername($_POST["username"]);
            if($result->password == md5($_POST["password"]))
            {
                $_SESSION["ntentan_logged_in"] = true;
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