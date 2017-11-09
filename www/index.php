<!DOCTYPE html>
<html>
<body>

<?php
try
{
    ini_set("display_errors", "stdout");
    ini_set("max_execution_time", "120");
    include "SQL.php";
    include "Test.php";
}
catch (Exception $ex)
{
    WriteDump($ex);
}
?>


<span>
    Testing!
</span>



</body>
</html>
