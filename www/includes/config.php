<?php
session_start();

require_once(__DIR__ . '/../' . 'includes/Environment.php');

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


require_once(__DIR__ . '/../' . 'classes/SQL.php');

$sql = new RedditSQLClient(DBHOST);
$sql->initializeSchema();


require_once(__DIR__ . '/../' . 'classes/Session.php');
$session = new Session($sql);


?>