<?php

require_once('classes/SiteUser.php');

class Session
{
    public $username;


    public function __construct()
    {
        $this->siteUser = null;
        if ($this->isAuthenticated())
        {
            $this->username = $_SESSION['username'];
        }
    }

    public function isAuthenticated()
    {
        return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
    }
}

?>