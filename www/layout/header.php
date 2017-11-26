<?php
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
?>

<html>

<head>
    <title><?php if (isset($title)) { echo $title; }?></title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/main.css">
</head>

<body>
