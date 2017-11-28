

<html>

<head>
    <title><?php if (isset($title)) { echo $title; }?></title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style/main.css">
</head>

<body>
<p>
    <b>
        <?php echo 'Welcome, ' . $session->username . '!'; ?>
    </b>
</p>
<ul list-style-type="square">
    <li><a href="/Portal/Home.php">Home</a></li>
    <li><a href="/Portal/Profile.php">Profile</a></li>
    <li><a href="/Logout.php">Logout</a></li>
</ul>