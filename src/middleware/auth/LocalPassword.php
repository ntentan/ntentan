<?php
namespace ntentan\middleware\auth;

/**
 * Description of LocalPassword
 *
 * @author ekow
 */
trait LocalPassword 
{
    public function validate($username, $password)
    {
        $users = Model::load($this->parameters->get('users_model', 'users'));
        $usernameField = $this->parameters->get('username_field', "username");
        $passwordField = $this->parameters->get('password_field', "password");
        $result = $users->filter("$usernameField = ?", $username)->fetchFirst();
        $passwordCrypt = $this->parameters->get(
            'password_crypt_function',
            function ($password, $storedPassword) {
                return password_verify($password, $storedPassword);
            }
        );
        if ($result && $passwordCrypt($password, $result->{$passwordField}) && $result->blocked != '1') {
            Session::set("logged_in", true);
            Session::set("username", $username);
            Session::set("user_id", $result["id"]);
            Session::set("user", $result->toArray());
            return true;
        } else {
            $this->setMessage($this->parameters->get('error_message', "Invalid username or password!"));
            return false;
        }
    }
}
