<?php
require_once(__DIR__ . '/../' . 'includes/config.php');



$session->RefreshData();

$title = 'Profile';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (isset($_POST['fname']))
{
    $session->user->fname = htmlspecialchars($_POST['fname']);
    $session->user->lname = htmlspecialchars($_POST['lname']);
    $session->user->age = (int)($_POST['age']);
    $session->user->telephone = htmlspecialchars($_POST['telephone']);
    $session->user->email = htmlspecialchars($_POST['email']);
    $session->user->address = htmlspecialchars($_POST['address']);

    $sql->updateSiteUserInfo($session->user);
}



?>

<br />
<br />
<br />
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
            <input id="age" name="age" placeholder="Age" value="<?php echo $session->user->age; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Telephone</b>
        </td>
        <td>
            <input id="telephone" name="telephone" placeholder="Telephone" value="<?php echo $session->user->telephone; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Email</b>
        </td>
        <td>
            <input id="email" name="email" placeholder="Email" value="<?php echo $session->user->email; ?>">
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