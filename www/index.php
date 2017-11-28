
<?php
require_once(__DIR__ . '/' . 'includes/config.php');

if ($session->isAuthenticated())
{
    header('Location: Portal/home.php');
    exit();
}
else
{
    header('Location: Login.php');
    exit();
}

?>

