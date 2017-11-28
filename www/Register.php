
<?php
require_once('includes/config.php');

//User is already logged in, redirect to Home
if ($session->isAuthenticated())
{
    header('Location: home.php');
    exit();
}


//This is a response request, try to authenticate user
if (isset($_POST['submit']))
{
    if (!isset($_POST['username']) || $_POST['username'] === '' || $_POST['username'] == null)
    {
        $err[] = "Please supply a username";
    }
    else if (!isset($_POST['password']))
    {
        $err[] = "Please supply a password";
    }
    else if (!isset($_POST['confirmpassword']))
    {
        $err[] = "Please confirm your password";
    }

    if (!isset($err))
    {
        $username = $_POST['username'];

        if (!SiteUser::ValidUsername($username))
        {
            $err[] = 'Invalid Characters in Username. Usernames may only contain letters and numbers';
        }
        else if (strlen($username) < 4)
        {
            $err[] = "Username must be at least 4 characters long";
        }
        else if ($sql->siteUserExists($username))
        {
            $err[] = "Username is not available";
        }

        

        if (strlen($_POST['password']) < 3)
        {
            $err[] = 'Password must be at least 3 characters long';
        }
        else if (strtolower($_POST['password']) === $_POST['password'])
        {
            $err[] = 'Password must contain at least one capital letter';
        }
        else if ($_POST['password'] !== $_POST['confirmpassword'])
        {
            $err[] = 'Passwords do not match';
        }


        //Validation is good, continue
        if (!isset($err))
        {
            $pass = SiteUser::HashPassword($_POST['password']);

            $sql->CreateSiteUser($username, $pass);
        }
    }

    
}

$title = 'Register';

require('layout/header.php');
?>



<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
            <form role="form" method="post" action="" autocomplete="off">

                <?php
                    //Display error(s)
                    if (isset($err))
                    {
                        foreach ($err as $error)
                        {
                            echo '<p class="bg-danger">' . $error . '</p>';
                        }
                    }
                ?>


                <div class="form-group">
                    <input type="text" name="username" id="username" class="form-control input-lg" placeholder="Username" value="<?php

                        if (isset($err))
                        {
                            echo htmlspecialchars($_POST['username'], ENT_QUOTES);
                        }
                    ?>" tabindex="1">
                </div>

                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
                        </div>
                            
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <input type="password" name="confirmpassword" id="confirmpassword" class="form-control input-lg" placeholder="Confirm Password" tabindex="4">
                        </div>
                            
                    </div>
                </div>



                <div class="row">
					<div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Register" class="btn btn-primary btn-block btn-lg" tabindex="5"></div>
				</div>
            </form>
        </div>
    </div>
</div>


<br />
<br />
<p>
    <a href="Login.php">Login</a>
</p>

<?php

require('layout/footer.php');

?>

