<?php

require_once(__DIR__ . '/../' . 'classes/SiteUser.php');
require_once(__DIR__ . '/../' . 'classes/SQL.php');

class LoginException extends Exception
{
    public $inner;

    public function __construct($ex)
    {
        Parent::__construct($ex);
        $inner = $ex;
    }
}

class Session
{
    public $username;
    public $sql;

    public function __construct($sql)
    {
        $this->sql = $sql;
        $this->siteUser = null;
        if ($this->isAuthenticated())
        {
            $this->username = $_SESSION['username'];
        }
    }

    public function isAuthenticated()
    {
        return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true);
    }

    public function Login($username, $password)
    {
        try
        {
            if (!$this->sql->siteUserExists($username))
            {
                throw new Exception('User does not exist!');
            }

            $hash = $this->sql->getPasswordHash_ByName($username);
            
            if (SiteUser::verifyPassword($hash, $password))
            {
                $this->username = $username;
                $_SESSION['username'] = $this->username;
                $_SESSION['loggedin'] = true;
            }
            else
            {
                throw new Exception('Invalid password!');
            }
        }
        catch (Exception $ex)
        {
            throw new LoginException($ex);
        }
    }

    public function Logout()
    {
        $_SESSION['username'] = null;
        $_SESSION['loggedin'] = false;
    }
}

?>