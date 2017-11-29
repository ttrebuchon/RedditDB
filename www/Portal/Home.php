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
                        <td><b>ID</b></td>
                        <td><b>Name</b></td>
                        <td><b>Comment Score</b></td>
                        <td><b>Link Score</b></td>
                    </tr>
                    <?php
                        $wUsers = $sql->GetWatchedUsers($session->user->name);
                        if ($wUsers !== null)
                        {
                            foreach ($wUsers as $usr)
                            {
                                echo '<tr><td>' . 
                                $usr->id . 
                                '</td><td>' . 
                                $usr->name . 
                                '</td><td>' .
                                $usr->comment_score .
                                '</td><td>' .
                                $usr->link_score .
                                '</td></tr>';
                            }
                        }
                    ?>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Subreddits</label>
            <div class="DataBox">
                <table id="WatchedSubreddits" border=1 class="ExpandTable">
                    <tr>
                        <td><b>Name</b></td>
                    </tr>
                    <?php
                        $wSubs = $sql->GetWatchedSubreddits($session->user->name);
                        if ($wSubs !== null)
                        {
                            foreach ($wSubs as $sub)
                            {
                                echo '<tr><td>' . 
                                '/r/' .
                                $sub . 
                                '</td></tr>';
                            }
                        }
                    ?>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Posts</label>
            <div class="DataBox">
                <table id="WatchedPosts" border=1 class="ExpandTable">
                    <tr>
                        <td><b>ID</b></td>
                        <td><b>Author</b></td>
                        <td><b>Title</b></td>
                        <td><b>Score</b></td>
                    </tr>
                    <?php
                        $wPosts = $sql->GetWatchedPosts($session->user->name);
                        if ($wPosts !== null)
                        {
                            foreach ($wPosts as $post)
                            {
                                echo '<tr><td>' . 
                                $post->id . 
                                '</td><td>' .
                                $post->author .
                                '</td><td>' .
                                $post->title .
                                '</td><td>' .
                                $post->score .
                                '</td></tr>';
                            }
                        }
                    ?>
                </table>
            </div>
        </td>
        <td>
            <label>Watched Comments</label>
            <div class="DataBox">
                <table id="WatchedComments" border=1 class="ExpandTable">
                    <tr>
                        <td><b>ID</b></td>
                        <td><b>Text</b></td>
                        <td><b>Score</b></td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>





<?php
require(__DIR__ . '/../' . 'layout/footer.php');

?>