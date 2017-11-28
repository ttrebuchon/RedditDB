<?php


class SiteUser
{

    public $name;

    public $permissions = [];

    const PermissionNames = ['ManageUsers', 'Backup', 'Restore', 'Edit'];




    public function __construct()
    {
        $this->name = null;
        foreach (SiteUser::PermissionNames as $name)
        {
            $this->permissions[$name] = null;
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