<?php
require_once(__DIR__ . '/../includes/config.php');

$title = 'Administrative';
require(__DIR__ . '/../' . 'layout/header_auth.php');

if (!$session->HasAdminPrivs())
{
    header('Location: /Portal/Home.php');
    exit();
}

?>

<label style="font-size:large">
    Data Deletion
</label>
<div class="SectionBoxBordered">
    <label style="text-align:center;width:100%">Obliterate Subreddit(s)</label>
    <form id="obliterate_subs_form" name="ObliterateSubsForm" role="form" method="post" action="" autocomplete="off" class="SectionBox">
        <textarea id="obliterate_subreddits" style="width:75%;resize:vertical"></textarea>
    </form>
    <input type="submit" value="Obliterate" onclick="obliterate_subs()">
</div>






<script src="/scripts/Admin.js"></script>

<?php

require(__DIR__ . '/../' . 'layout/footer.php');


?>