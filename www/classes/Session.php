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
    public $user;
    public $sql;

    public function __construct($sql)
    {
        $this->sql = $sql;
        $this->user = new SiteUser();
        if ($this->isAuthenticated())
        {
            $this->user->name = $_SESSION['username'];
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
                $this->user->name = $username;
                $_SESSION['username'] = $this->user->name;
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
        $_SESSION['canBackup'] = null;
        $_SESSION['canRestore'] = null;
        $_SESSION['canEdit'] = null;
        $_SESSION['canManageUsers'] = null;
        $this->user = new SiteUser();
    }

    public function HasAdminPrivs()
    {
        foreach ($this->user->permissions as $perm)
        {
            
            if ($perm === true)
            {
                return true;
            }
            else if ($perm === null)
            {
                $this->RefreshData();
                return $this->HasAdminPrivs();
            }
        }
        return false;
    }

    private function RefreshData()
    {
        $this->sql->getSiteUserData($this->user->name, $this->user);
    }
}

?>