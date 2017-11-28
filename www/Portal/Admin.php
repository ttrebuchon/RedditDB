<?php
require_once(__DIR__ . '/../includes/config.php');

$title = 'Administrative';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (!$session->HasAdminPrivs())
{
    header('Location: /Portal/Home.php');
    exit();
}



?>



<?php

require(__DIR__ . '/../' . 'layout/footer.php');


?>