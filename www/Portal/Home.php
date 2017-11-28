<?php
require_once(__DIR__ . '/../' . 'includes/config.php');


if (!$session->isAuthenticated())
{
    header('Location: Login.php');
    exit();
}


$title = 'Home';
require(__DIR__ . '/../' . 'layout/header_auth.php');

?>

<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>