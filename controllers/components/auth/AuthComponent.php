<?php
/**
 * 
 */
class AuthComponent extends Component
{
    public $loginPath = "users/login";
    public $logoutPath = "users/logout";
    public $redirectPath = "/";
    public $name = __CLASS__;
    public $authMethod = "http_post";
    protected $authMethodInstance;
    
    public function preRender()
    {
        // Load the authenticator
        $authenticatorClass = Ntentan::camelize($this->authMethod);
        $this->authMethodInstance = new $authenticatorClass();        
        
        // Allow the roles component to activate the authentication if it is
        // available. If not just run the authenticator from this section.
        if($this->controller->hasComponent("roles")) return;
        if($_SESSION["logged_in"] == false)
        {
            $this->login();
        }
    }
    
    public function login()
    {
        $this->authMethodInstance->login();
        /*if(isset($_POST["username"]) && isset($_POST["password"]))
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
        }*/
    }

    public function logout()
    {
        $_SESSION = array();
        Ntentan::redirect($this->redirectPath);
    }
}