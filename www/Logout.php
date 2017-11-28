<?php
require_once(__DIR__ . '/' . 'includes/config.php');

$session->Logout();

header('Location: Login.php');
exit();

?>