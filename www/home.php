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

<a href="Logout.php">Logout</a>

<?php
require('layout/footer.php');


?>