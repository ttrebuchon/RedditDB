<?php


class SiteUser
{
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

    private static function verifyPassword($hash, $password)
    {
        return password_verify($password, $hash);
    }
}

?>