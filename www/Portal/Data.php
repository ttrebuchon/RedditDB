<?php
require_once(__DIR__ . '/../includes/config.php');

$title = 'Data';
require(__DIR__ . '/../' . 'layout/header_auth.php');

var_export($_POST);

$searched = false;
foreach ($_POST as $param)
{
    if ($param != null && $param !== '')
    {
        $searched = true;
        break;
    }
}

if ($searched)
{
    //If uncommented, there are no confirmations for form resubmissions,
    //but search data will be cleared between refreshes

    // echo "
    // <script>
    //     window.onload = function() {
    //         history.replaceState('', '', '/Portal/Data.php');
    //     };
    // </script>";
}

function AuthorCol($name)
{
    return "<td><a href='http://www.reddit.com/u/{$name}'>{$name}</a></td>";
}

function SubredditCol($name)
{
    return "<td><a href='http://www.reddit.com/r/{$name}'>/r/{$name}</a></td>";
}

?>

<form id="DataForm" name="DataForm" role="form" method="post">
<table>
    <tr>
        <td>
            <label>Subreddit</label>
        </td>
        <td>
            <input id="Subreddits_Search" name="Subreddits_Search" type="text" value="<?php echo $_POST['Subreddits_Search']; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <label>Username</label>
        </td>
        <td>
            <input id="Users_Name_Search" name="Users_Name_Search" type="text" value="<?php echo $_POST['Users_Name_Search']; ?>">
        </td>
        <td>
            <label>Comment Score >= </label>
        </td>
        <td>
            <input id="Users_CommentScore_GTE_Search" name="Users_CommentScore_GTE_Search" type="number" value="<?php echo $_POST['Users_CommentScore_GTE_Search']; ?>">
        </td>
        <td>
            <label>Comment Score <= </label>
        </td>
        <td>
            <input id="Users_CommentScore_LTE_Search" name="Users_CommentScore_LTE_Search" type="number" value="<?php echo $_POST['Users_CommentScore_LTE_Search']; ?>">
        </td>
        <td>
            <label>Link Score >= </label>
        </td>
        <td>
            <input id="Users_LinkScore_GTE_Search" name="Users_LinkScore_GTE_Search" type="number" value="<?php echo $_POST['Users_LinkScore_GTE_Search']; ?>">
        </td>
        <td>
            <label>Link Score <= </label>
        </td>
        <td>
            <input id="Users_LinkScore_LTE_Search" name="Users_LinkScore_LTE_Search" type="number" value="<?php echo $_POST['Users_LinkScore_LTE_Search']; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <label>Post Title</label>
        </td>
        <td>
            <input id="Posts_Title_Search" name="Posts_Title_Search" type="text" value="<?php echo $_POST['Posts_Title_Search']; ?>">
        </td>
        <td>
            <label>Score >=</label>
        </td>
        <td>
            <input id="Posts_Score_GTE_Search" name="Posts_Score_GTE_Search" type="number" value="<?php echo $_POST['Posts_Score_GTE_Search']; ?>">
        </td>
        <td>
            <label>Score <=</label>
        </td>
        <td>
            <input id="Posts_Score_LTE_Search" name="Posts_Score_LTE_Search" type="number" value="<?php echo $_POST['Posts_Score_LTE_Search']; ?>">
        </td>
    </tr>
    <tr>
        <td>
            <label>Comment Score >=</label>
        </td>
        <td>
            <input id="Comments_Score_GTE_Search" name="Comments_Score_GTE_Search" type="number" value="<?php echo $_POST['Comments_Score_GTE_Search']; ?>">
        </td>
        <td>
            <label>Comment Score <=</label>
        </td>
        <td>
            <input id="Comments_Score_LTE_Search" name="Comments_Score_LTE_Search" type="number" value="<?php echo $_POST['Comments_Score_LTE_Search']; ?>">
        </td>
    </tr>
</table>
<input type="submit" value="Search">
</form>

<br />
<br />

<table class="ExpandTable">
    <tr>
        <td>
            <label>Subreddits</label>
            <div class="DataBox">
                <table class="ExpandTable" border=1>
                    <thead>
                        <tr>
                            <td><b>Name</b></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ($searched)
                            {
                                if ($_POST['Subreddits_Search'] !== '')
                                {
                                    foreach (
                                        $sql->dataSearch_Subreddits(
                                            htmlspecialchars($_POST['Subreddits_Search'])
                                            )
                                        as $sub)
                                    {
                                        echo "<tr><td><a href='http://www.reddit.com/r/{$sub}'>/r/{$sub}</a></td></tr>";
                                    }
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </td>
        <td>
            <label>Users</label>
            <div class="DataBox">
                <table class="ExpandTable" border=1>
                    <thead>
                        <tr>
                            <td><b>Name</b></td>
                            <td><b>Comment Score</b></td>
                            <td><b>Link Score</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                            if ($searched)
                            {
                                if ($_POST['Users_Name_Search'] !== '' || 
                                    $_POST['Users_CommentScore_GTE_Search'] !== '' || 
                                    $_POST['Users_CommentScore_LTE_Search'] !== '' || 
                                    $_POST['Users_LinkScore_GTE_Search'] !== '' || 
                                    $_POST['Users_LinkScore_LTE_Search'] !== '')
                                {
                                    foreach (
                                        $sql->dataSearch_Users(
                                            htmlspecialchars($_POST['Users_Name_Search']),
                                            htmlspecialchars($_POST['Users_CommentScore_GTE_Search']),
                                            htmlspecialchars($_POST['Users_CommentScore_LTE_Search']),
                                            htmlspecialchars($_POST['Users_LinkScore_GTE_Search']),
                                            htmlspecialchars($_POST['Users_LinkScore_LTE_Search'])
                                            )
                                        as $usr)
                                    {
                                        echo "<tr>";
                                        echo AuthorCol($usr['user_name']);
                                        echo "<td>{$usr['comment_score']}</td>";
                                        echo "<td>{$usr['link_score']}</td>";
                                        echo "</tr>";
                                    }
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </td>
        <td>
            <label>Posts</label>
            <div class="DataBox">
                <table class="ExpandTable" border=1>
                    <thead>
                        <tr>
                            <td><b>ID</b></td>
                            <td><b>Author</b></td>
                            <td><b>Time</b></td>
                            <td><b>Title</b></td>
                            <td><b>Subreddit</b></td>
                            <td><b>Score</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                            if ($searched)
                            {
                                foreach (
                                    $sql->dataSearch_Posts(
                                        htmlspecialchars($_POST['Subreddits_Search']),
                                        htmlspecialchars($_POST['Users_Name_Search']),
                                        htmlspecialchars($_POST['Users_CommentScore_GTE_Search']),
                                        htmlspecialchars($_POST['Users_CommentScore_LTE_Search']),
                                        htmlspecialchars($_POST['Users_LinkScore_GTE_Search']),
                                        htmlspecialchars($_POST['Users_LinkScore_LTE_Search']),
                                        htmlspecialchars($_POST['Posts_Title_Search']),
                                        htmlspecialchars($_POST['Posts_Score_GTE_Search']),
                                        htmlspecialchars($_POST['Posts_Score_LTE_Search'])
                                        )
                                    as $post)
                                {
                                    echo "<tr>";
                                    echo "<td><a href='http://www.reddit.com{$post['permalink']}'>{$post['id']}</a></td>";
                                    echo AuthorCol($post['author']);
                                    echo "<td>{$post['time']}</td>";
                                    echo "<td><a href='http://www.reddit.com{$post['permalink']}'>{$post['title']}</a></td>";
                                    echo SubredditCol($post['subreddit_name']);
                                    echo "<td>{$post['score']}</td>";
                                    echo "</tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </td>
        <td>
            <label>Comments</label>
            <div class="DataBox">
                <table class="ExpandTable" border=1>
                    <thead>
                        <tr>
                            <td><b>ID</b></td>
                            <td><b>Author</b></td>
                            <td><b>Time</b></td>
                            <td><b>Post</b></td>
                            <td><b>Subreddit</b></td>
                            <td><b>Score</b></td>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                            if ($searched)
                            {
                                foreach (
                                    $sql->dataSearch_Comments(
                                        htmlspecialchars($_POST['Subreddits_Search']),
                                        htmlspecialchars($_POST['Users_Name_Search']),
                                        htmlspecialchars($_POST['Users_CommentScore_GTE_Search']),
                                        htmlspecialchars($_POST['Users_CommentScore_LTE_Search']),
                                        htmlspecialchars($_POST['Users_LinkScore_GTE_Search']),
                                        htmlspecialchars($_POST['Users_LinkScore_LTE_Search']),
                                        htmlspecialchars($_POST['Posts_Title_Search']),
                                        htmlspecialchars($_POST['Posts_Score_GTE_Search']),
                                        htmlspecialchars($_POST['Posts_Score_LTE_Search']),
                                        htmlspecialchars($_POST['Comments_Score_GTE_Search']),
                                        htmlspecialchars($_POST['Comments_Score_LTE_Search'])
                                        )
                                    as $post)
                                {
                                    echo "<tr>";
                                    echo "<td><a href='http://www.reddit.com{$post['permalink']}'>{$post['id']}</a></td>";
                                    echo AuthorCol($post['author']);
                                    echo "<td>{$post['time']}</td>";
                                    echo "<td>{$post['title']}</td>";
                                    echo SubredditCol($post['subreddit_name']);
                                    echo "<td>{$post['score']}</td>";
                                    echo "</tr>";
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
</table>


<?php

require(__DIR__ . '/../' . 'layout/footer.php');

?>