<?php
require_once(__DIR__ . '/../' . 'includes/config.php');


if (!$session->isAuthenticated())
{
    header('Location: Login.php');
    exit();
}


$title = 'Home';
require(__DIR__ . '/../' . 'layout/header.php');

?>

<ul list-style-type="square">
<li><a href="Profile.php">Profile</a></li>
<li><a href="../Logout.php">Logout</a></li>
</ul>


<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>