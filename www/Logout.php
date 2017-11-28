<?php
require_once('includes/config.php');

$session->Logout();

header('Location: Login.php');
exit();

?>