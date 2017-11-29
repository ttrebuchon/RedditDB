<?php
require_once(__DIR__ . '/../' . 'includes/config.php');


$title = 'Home';
require(__DIR__ . '/../' . 'layout/header_auth.php');

?>

<table class="ExpandTable">
    <tr>
        <td>
            <label>Watched Users</label>
            <div class="DataBox">
                <table id="WatchedUsers" border=1 class="ExpandTable">
                    <tr>
                        <td>Name</td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Subreddits</label>
            <div class="DataBox">
                <table id="WatchedSubreddits" border=1 class="ExpandTable">
                    <tr>
                        <td>Name</td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Posts</label>
            <div class="DataBox">
                <table id="WatchedPosts" border=1 class="ExpandTable">
                    <tr>
                        <td>ID</td>
                        <td>Name</td>
                    </tr>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Comments</label>
            <div class="DataBox">
                <table id="WatchedComments" border=1 class="ExpandTable">
                    <tr>
                        <td>ID</td>
                        <td>Text</td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>





<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>