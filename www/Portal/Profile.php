<?php
require_once(__DIR__ . '/../' . 'includes/config.php');

$session->RefreshData();

$title = 'Profile';
require(__DIR__ . '/../' . 'layout/header_auth.php');



?>



<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>