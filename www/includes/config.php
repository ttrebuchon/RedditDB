<?php

$DEBUG = true;

require_once('includes/Creds.php');
require_once('classes/SQL.php');
include('classes/Session.php');

$sql = new RedditSQLClient(DBHOST);
$session = new Session();


?>