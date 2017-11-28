<?php



require_once('includes/Environment.php');

if (isset($DEBUG) && $DEBUG === true)
{
   ini_set("display_errors", "stdout");
   error_reporting(E_ERROR | E_WARNING | E_PARSE);
}
else
{
    ini_set("display_errors", "off");
    error_reporting(0);
}


require_once('classes/SQL.php');
include('classes/Session.php');

$sql = new RedditSQLClient(DBHOST);
$session = new Session();


?>