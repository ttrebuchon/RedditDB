<!DOCTYPE html>
<html>
<body>

<?php
try
{
    ini_set("display_errors", "stdout");
    ini_set("max_execution_time", "30");
    include "SQL.php";
    include "Test.php";
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



</body>
</html>
