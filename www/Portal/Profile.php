<?php
require_once(__DIR__ . '/../' . 'includes/config.php');

$session->RefreshData();

$title = 'Profile';
require(__DIR__ . '/../' . 'layout/header_auth.php');



?>

<br />
<br />
<br />
<table border=1>
    <tr>
        <td>
            <b>First Name</b>
        </td>
        <td>
            <input id="fname" placeholder="First Name" value="<?php echo $session->user->fname; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Last Name</b>
        </td>
        <td>
            <input id="lname" placeholder="Last Name" value="<?php echo $session->user->lname; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Age</b>
        </td>
        <td>
            <input id="age" placeholder="Age" value="<?php echo $session->user->age; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Telephone</b>
        </td>
        <td>
            <input id="telephone" placeholder="Telephone" value="<?php echo $session->user->telephone; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Email</b>
        </td>
        <td>
            <input id="email" placeholder="Email" value="<?php echo $session->user->email; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <b>Address</b>
        </td>
        <td>
            <input id="address" placeholder="Address" value="<?php echo $session->user->address; ?>">
        </td>
    </tr>
    <tr><td><input id="submit" type="submit" name="submit" value="Apply"></td></tr>
</table>

<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>