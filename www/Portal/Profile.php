<?php
require_once(__DIR__ . '/../' . 'includes/config.php');



$session->RefreshData();

$title = 'Profile';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (isset($_POST['fname']))
{
    if (htmlspecialchars($_POST['fname']) != null)
    {
        $session->user->fname = htmlspecialchars($_POST['fname']);
    }
    else
    {
        $session->user->fname = null;
    }

    if (htmlspecialchars($_POST['lname']) != null)
    {
        $session->user->lname = htmlspecialchars($_POST['lname']);
    }
    else
    {
        $session->user->lname = null;
    }

    if ($_POST['age'] != null)
    {
        $session->user->age = (int)$_POST['age'];
    }
    else
    {
        $session->user->age = null;
    }

    if (htmlspecialchars($_POST['telephone']) != null)
    {
        $session->user->telephone = htmlspecialchars($_POST['telephone']);
    }
    else
    {
        $session->user->telephone = null;
    }
    
    if (htmlspecialchars($_POST['email']) != null)
    {
        $session->user->email = htmlspecialchars($_POST['email']);
    }
    else
    {
        $session->user->email = null;
    }

    if (htmlspecialchars($_POST['address']) != null)
    {
        $session->user->address = htmlspecialchars($_POST['address']);
    }
    else
    {
        $session->user->address = null;
    }
    

    $sql->updateSiteUserInfo($session->user);
}



?>


<form id="InfoForm" name="InfoForm" role="form" method="post" action="" autocomplete="off">
<table border=1>
    <tr>
        <td>
            <b>First Name</b>
        </td>
        <td>
            <input id="fname" name="fname" placeholder="First Name" value="<?php echo $session->user->fname; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Last Name</b>
        </td>
        <td>
            <input id="lname" name="lname" placeholder="Last Name" value="<?php echo $session->user->lname; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Age</b>
        </td>
        <td>
            <input id="age" name="age" placeholder="Age" type="number" value="<?php echo $session->user->age; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Telephone</b>
        </td>
        <td>
            <input id="telephone" name="telephone" placeholder="Telephone" type="tel" value="<?php echo $session->user->telephone; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Email</b>
        </td>
        <td>
            <input id="email" name="email" placeholder="Email" type="email" value="<?php echo $session->user->email; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Address</b>
        </td>
        <td>
            <input id="address" name="address" placeholder="Address" value="<?php echo $session->user->address; ?>">
        </td>
    </tr>
    
    <tr><td><input id="apply" name="apply" type="button" value="Apply" onclick="Apply()"></td></tr>
</table>
</form>
<label id="ErrorMsg"></label>


<script src="/scripts/Profile.js"></script>

<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>