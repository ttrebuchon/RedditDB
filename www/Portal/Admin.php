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
    $backup = false;
    $restore = false;
    $edit = false;
    $user = null;
    $perms = [];

    if (array_key_exists('update_user_username', $_POST))
    {
        $user = htmlspecialchars($_POST['update_user_username']);

        foreach (SiteUser::PermissionNames as $name)
        {
            $permName = "update_user_{$name}";
            $perms[$name] = false;
            if (isset($_POST[$permName]))
            {
                if ($_POST[$permName] === 'on')
                {
                    $perms[$name] = true;
                }
            }
        }

        if (strtolower($user) !== 'admin')
        {
            $sql->updateSiteUserPermissions($user, $perms);
            $session->RefreshData();
        }
    }
}

if ($session->user->permissions['Backup'] == true)
{
    if (array_key_exists('backup_db_action', $_POST))
    {
        if ($_POST['backup_db_action'] == 1)
        {
            $sql->backup();
            echo '<p>Backed Up!</p>';
        }
    }
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
    <table class="ExpandTable">
        <tr>
            <td>
                <label style="text-align:center;width:100%">User Permissions</label>
                <form id="user_permissions_form" name="UserPermissionsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
                    <input id="update_user_username" name="update_user_username" type="text" placeholder="Username">
                    <br />
                    Manage Accounts
                    <input id="update_user_ManageUsers" name="update_user_ManageUsers" type="checkbox">
                    <br />
                    Backup Database
                    <input id="update_user_Backup" name="update_user_Backup" type="checkbox">
                    <br />
                    Restore Database
                    <input id="update_user_Restore" name="update_user_Restore" type="checkbox">
                    <br />
                    Edit Data
                    <input id="update_user_Edit" name="update_user_Edit" type="checkbox">
                    <br />
                    <input type="button" value="UpdateUser" onclick="updateUser()">
                </form>
                
            </td>
            <td>
                <table border=1 style="width:auto;width:100%">
                    <thead></thead>
                    <tbody class="UsersTableBody">
                        <?php
                            foreach ($sql->getSiteUserNames() as $username)
                            {
                                echo '<tr class="UsersRow"><td class="UsersRow">' . $username . '</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>


<?php endif; ?>


<?php if ($session->user->permissions['Backup'] == true): ?>
<label style="font-size:large">
    Database Backup
</label>


<div class="SectionBoxBordered">
        <form id="backup_db_form" name="BackupDBForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
            <input id="backup_db_action" name="backup_db_action" type="hidden" value=0>
        </form>
    <input type="submit" value="Backup" onclick="backupDB()">
</div>


<?php endif; ?>



<script src="/scripts/Admin.js"></script>

<?php

require(__DIR__ . '/../' . 'layout/footer.php');


?>