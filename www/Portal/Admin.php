<?php
require_once(__DIR__ . '/../includes/config.php');

$title = 'Administrative';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (!$session->HasAdminPrivs())
{
    header('Location: /Portal/Home.php');
    exit();
}


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


?>

<label style="font-size:large">
    Data Deletion
</label>


<div class="SectionBoxBordered">
    <label style="text-align:center;width:100%">Obliterate Subreddit(s)</label>
    <form id="obliterate_subs_form" name="ObliterateSubsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
        <textarea id="obliterate_subreddits" name="obliterate_subreddits" style="width:75%;resize:vertical"></textarea>
    </form>
    <input type="submit" value="Obliterate" onclick="obliterateSubs()">
</div>

<br />
<br />

<div class="SectionBoxBordered">
    <label style="text-align:center;width:100%">Obliterate Post(s) (By ID)</label>
    <form id="obliterate_posts_form" name="ObliteratePostsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
        <textarea id="obliterate_posts" name="obliterate_posts" style="width:75%;resize:vertical"></textarea>
    </form>
    <input type="submit" value="Obliterate" onclick="obliteratePosts()">
</div>






<script src="/scripts/Admin.js"></script>

<?php

require(__DIR__ . '/../' . 'layout/footer.php');


?>