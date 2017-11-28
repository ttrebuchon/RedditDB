<?php
require_once('includes/config.php');

if (!$session->isAuthenticated())
{
    header('Location: Login.php');
    exit();
}


$title = 'Home';
require('layout/header.php');

?>

Hello?

<?php
require('layout/footer.php');


?>