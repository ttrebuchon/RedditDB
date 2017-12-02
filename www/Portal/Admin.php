<?php
require_once(__DIR__ . '/../includes/config.php');

$session->RefreshData();

$title = 'Administrative';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (!$session->HasAdminPrivs())
{
    header('Location: /Portal/Home.php');
    exit();
}

if ($session->user->permissions['Edit'] == true)
{
    if (array_key_exists('obliterate_subreddits', $_POST))
    {
        $subs = json_decode($_POST['obliterate_subreddits']);
        foreach ($subs as $sub)
        {
            $sql->obliterateSubreddit($sub);
        }
        
    }
    
    if (array_key_exists('obliterate_posts', $_POST))
    {
        $posts = json_decode($_POST['obliterate_posts']);
        foreach ($posts as $post)
        {
            $sql->obliteratePost($post);
        }
    }
}

if ($session->user->permissions['ManageUsers'] == true)
{
    $manageUsers = false;
    if (array_key_exists('update_user_username', $_POST))
    {
        if (array_key_exists('update_user_accountmng', $_POST))
        {
            if ($_POST['update_user_accountmng'] === 'on')
            {
                $manageUsers = true;
            }
        }
    }
    var_export($_POST);
}


?>

<?php if ($session->user->permissions['Edit'] == true): ?>
<label style="font-size:large">
    Data Deletion
</label>


<div class="SectionBoxBordered">
    <label style="text-align:center;width:100%">Obliterate Subreddit(s)</label>
        <form id="obliterate_subs_form" name="ObliterateSubsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
            <textarea id="obliterate_subreddits" name="obliterate_subreddits" style="width:75%;resize:vertical"></textarea>
        </form>
    <input type="submit" value="Obliterate" onclick="obliterateSubs()">

    <br />
    <br />

    <label style="text-align:center;width:100%">Obliterate Post(s) (By ID)</label>
        <form id="obliterate_posts_form" name="ObliteratePostsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
            <textarea id="obliterate_posts" name="obliterate_posts" style="width:75%;resize:vertical"></textarea>
        </form>
    <input type="submit" value="Obliterate" onclick="obliteratePosts()">
</div>


<?php endif; ?>




<?php if ($session->user->permissions['ManageUsers'] == true): ?>
<br />
<br />
<br />
<br />


<label style="font-size:large">
    Account Management
</label>
<div class="SectionBoxBordered">
    <label style="text-align:center;width:100%">User Permissions</label>
    <form id="user_permissions_form" name="UserPermissionsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
        <input id="update_user_username" name="update_user_username" type="text" placeholder="Username">
        <br />
        Manage Accounts
        <input id="update_user_accountmng" name="update_user_accountmng" type="checkbox">
        <br />
        Backup Database
        <input id="update_user_backup" name="update_user_backup" type="checkbox">
        <br />
        Restore Database
        <input id="update_user_restore" name="update_user_restore" type="checkbox">
        <br />
        Edit Data
        <input id="update_user_edit" name="update_user_edit" type="checkbox">
    </form>
    <input type="submit" value="UpdateUser" onclick="updateUser()">
</div>


<?php endif; ?>



<script src="/scripts/Admin.js"></script>

<?php

require(__DIR__ . '/../' . 'layout/footer.php');


?>