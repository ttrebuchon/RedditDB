<?php


class SiteUser
{

    public $name;

    public $permissions = [];

    public static $PermissionNames = ['ManageUsers', 'Backup', 'Restore', 'Edit'];

    public $fname;
    public $lname;
    public $age;
    public $telephone;
    public $email;
    public $address;

    function __get($name)
    {
        if (substr($name, 0, 3) === 'can')
        {
            if (array_key_exists(substr($name, 3), $this->permissions))
            {
                return $this->permissions[substr($name, 3)];
            }
            else
            {
                throw new Exception('Permission does not exists "' . $name . '"');
            }
        }
        
        return null;
    }


    public function __construct()
    {
        $this->name = null;
        foreach (SiteUser::$PermissionNames as $name)
        {
            $this->permissions[$name] = null;
            if (array_key_exists($name, $_SESSION))
            {
                $this->permissions[$name] = $_SESSION[$name];
            }
        }
    }



    public static function ValidUsername($name)
    {
        //Check if alphanumeric only
        if (!ctype_alnum($name))
        {
            return false;
        }


        return true;
    }

    public static function HashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($hash, $password)
    {
        return password_verify($password, $hash);
    }
}

?>