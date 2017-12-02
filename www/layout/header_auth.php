<?php
if (!$session->isAuthenticated())
{
    header('Location: /Login.php');
    exit();
}

?>

<!DOCTYPE HTML>
<html>

<head>
    <title><?php if (isset($title)) { echo $title; }?></title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ROOT_DIR; ?>/style/main.css">
    
</head>

<body>
<div class="HeadLinks">
    <p>
        <b>
            <?php echo 'Welcome, ' . $session->user->name . '!'; ?>
        </b>
    </p>
    <ul list-style-type="square">
        <li><a href="<?php echo ROOT_DIR; ?>/Portal/Home.php">Home</a></li>
        <?php
            if ($session->hasAdminPrivs())
            {
                echo '<li><a href="' . ROOT_DIR . '/Portal/Admin.php">Administrator</a></li>';
            }
        ?>
        <li><a href="<?php echo ROOT_DIR; ?>/Portal/Data.php">Data</a></li>
        <li><a href="<?php echo ROOT_DIR; ?>/Portal/Profile.php">Profile</a></li>
        <li><a href="<?php echo ROOT_DIR; ?>/Logout.php">Logout</a></li>
    </ul>
</div>
<hr>
<div class="MainPage">