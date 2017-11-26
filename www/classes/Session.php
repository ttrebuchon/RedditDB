<?php

require_once('classes/SiteUser.php');

class Session
{
    public $siteUser;


    public function __construct()
    {
        $this->siteUser = null;
        if ($this->isAuthenticated())
        {
            $this->siteUser = new SiteUser();
            $this->siteUser->usr = $_SESSION['username'];
        }
    }

    public function isAuthenticated()
    {
        return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
    }
}

?>