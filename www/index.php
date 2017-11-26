
<?php
require_once('includes/config.php');

if ($session->isAuthenticated())
{
    header('Location: home.php');
    exit();
}

$title = "Index";

require('layout/header.php');

ini_set("display_errors", "stdout");
//ini_set("max_execution_time", "30");
set_time_limit(30);
try
{
    
}
catch (Exception $ex)
{
    echo '<pre>' . var_export($ex, true)."\n" . '</pre>';
}
?>

<br />
<span>
    Testing!
</span>

<?php

require('layout/footer.php');

?>

