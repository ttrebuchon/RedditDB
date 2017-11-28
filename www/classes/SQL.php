<?php

require_once('classes/Schema.php');

function Exc($msg)
{
    throw new Exception($msg);
}

function SQL_Exc($obj)
{
    throw new Exception(htmlspecialchars($obj->error));
}

class RedditSQLClient
{
    private $connection;
    public $schema;

    function __construct($host, $db = null)
    {
        $this->schema = new DBSchema();
        $this->connection = new mysqli($host, DBUSER, DBPASS, $db);

        if (false === $this->connection) {
            throw new Exception("Unable to initialize MySQL interface");
        }

        if ($this->connection->connect_error) {
            throw new Exception(htmlspecialchars($this->connection->connect_error));
            //die("Connection failed! " . $this->connection->connect_error);
        }
    }

    function __destruct()
    {
        $this->connection->close();
    }

    function isOpen()
    {
        return !($this->connection->connect_error);
    }

    function query($query)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $result = $this->connection->query($query);

        if (false === $result) {
            SQL_Exc($this->connection);
        }
        return $result;
    }

    function queryToArray($query)
    {
        $result = $this->query($query);

        if (!$result) {
            return;
        }

        if ($result->num_rows > 0) {
            $arr = array();

            while ($row = $result->fetch_assoc()) {
                array_push($arr, $row);
            }
            return $arr;
        }
    }

    function initializeSchema()
    {
        return $this->schema->Initialize($this);
    }

    function dropSchema()
    {
        return $this->schema->Drop($this);
    }

    function UserStored_ByID($id)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->DatabaseName
                . "." . $this->schema->UsersName . " WHERE id = ?"
            ) or Exc($this->connection->error_get_last);
        }
            

        $query->bind_param("s", $id);

        $query->execute() or Exc($this->connection->error_get_last);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function UsersStored_ByID($ids)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $len = count($ids);

        if ($len <= 0) {
            return [];
        }

        $paramString = str_repeat("s", $len);
        $paramList = "(?";
        for ($i = 1; $i < $len; $i = $i + 1) {
            $paramList = $paramList . ", ?";
        }
        $paramList = $paramList . ")";

        $query = "
                SELECT id FROM " . $this->schema->DatabaseName . "."
            . $this->schema->UsersName . " WHERE id IN " . $paramList;
            

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            throw new Exception(htmlspecialchars($this->connection->error));
        }

        foreach ($ids as $key => &$value) {
            $params[] =& $value;
        }

        $rc = call_user_func_array(
            array($stmt, "bind_param"),
            array_merge(
                array($paramString),
                $params
            )
        );

        if (false === $rc) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        return array_column($result, 0);
    }

    function UserStored_ByName($name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->DatabaseName
                . "." . $this->schema->UsersName . " WHERE user_name = ?"
            ) or SQL_Exc($this->connection);
        }
            

        $query->bind_param("s", $name);

        $query->execute() or SQL_Exc($this->connection);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function GetUserID($name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT id FROM " . $this->schema->DatabaseName
                . "." . $this->schema->UsersName . " WHERE user_name = ?"
            ) or SQL_Exc($this->connection);
        }

        $query->bind_param("s", $name);
        
        $query->execute() or SQL_Exc($this->connection);

        $result = $query->get_result()->fetch_array()[0];
        return $result;
    }

    function UsersStored_ByName($names)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $len = count($names);

        if ($len <= 0) {
            return [];
        }

        $paramString = str_repeat("s", $len);
        $paramList = "(?";
        for ($i = 1; $i < $len; $i = $i + 1) {
            $paramList = $paramList . ", ?";
        }
        $paramList = $paramList . ")";

        $query = "
                SELECT user_name FROM " . $this->schema->DatabaseName . "."
            . $this->schema->UsersName . " WHERE user_name IN " . $paramList;
            

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            throw new Exception(htmlspecialchars($this->connection->error));
        }

        foreach ($names as $key => &$value) {
            $params[] =& $value;
        }

        $rc = call_user_func_array(
            array($stmt, "bind_param"),
            array_merge(
                array($paramString),
                $params
            )
        );

        if (false === $rc) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        return array_column($result, 0);
    }

    function addUser($usr)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        if ($usr === null || gettype($usr) !== 'object')
        {
            throw new Exception("Tried to add null user: '" . var_export($usr, true) . "'");
        }

        // $query =
        //     "INSERT INTO " . $this->schema->UsersTable()
        //     . " (
        //         id,
        //         user_name,
        //         utc_created,
        //         link_score,
        //         comment_score
        //     ) VALUES (
        //         ?,
        //         ?,
        //         FROM_UNIXTIME(?),
        //         ?,
        //         ?
        //     )";
        $query = 'CALL ' . $this->schema->Database('AddUser') . '(?, ?, ?, ?, ?)';
        
        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            SQL_Exc($this->connection);
        }

        if (false === $stmt->bind_param("ssiii",
            $usr->name,
            $usr->id,
            $usr->utc_created,
            $usr->link_score,
            $usr->comment_score)
        ) {
            SQL_Exc($stmt);
        }

        if (false === $stmt->execute()) {
            SQL_Exc($stmt);
        }
    }

    function addUsers($users)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        

        try
        {
            $this->startTransaction();

            foreach ($users as $user)
            {
                if ($user !== null)
                {
                    $this->addUser($user);
                }
                
            }
        }
        catch (Exception $ex)
        {
            $this->endTransaction();
            throw $ex;
        }
        $this->endTransaction();
    }

    function GetUsers($count)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query =
            "SELECT * FROM " . $this->schema->UsersTable();
        if ($count > 0)
        {
            $query = $query . " LIMIT ?";
        }

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            SQL_Exc($this->connection);
        }

        if ($count > 0)
        {
            if (false === $stmt->bind_param("i", $count)) {
                SQL_Exc($stmt);
            }
        }

        

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $users = [];

        foreach ($result as $row)
        {
            $usr = new User();
            $usr->name = $row[0];
            $usr->id = $row[1];
            $usr->utc_timestamp = $row[2];
            $usr->link_score = $row[3];
            $usr->comment_score = $row[4];
            
            array_push($users, $usr);
        }

        return $users;
    }

    function addPost($Reddit, $post)
    {
        if ($post === null)
        {
            return;
        }
        else if ($post->id === null)
        {
            return;
        }


        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "INSERT INTO " . $this->schema->PostsTable()
            . " (
                id,
                author,
                title,
                creation_timestamp,
                score,
                permalink,
                link,
                subreddit_id,
                text,
                text_html
            ) VALUES (
                ?,
                ?,
                ?,
                FROM_UNIXTIME(?),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            ) ON DUPLICATE KEY UPDATE 
            score = VALUES(score), 
            permalink = VALUES(permalink),
            text = VALUES(text),
            text_html = VALUES(text_html)";
        
        $stmt = $this->connection->prepare($query);

        if ($post->author === null)
        {

        }
        else if ($post->author === '[deleted]')
        {
            $post->author = null;
        }
        else if (!$this->UserStored_ByName($post->author))
        {
            try
            {
                $author = $Reddit->GetUser($post->author);
                $this->AddUser($author);
            }
            catch (RedditAPIException $ex)
            {
                if (strpos($ex->msg, 'does not exist') !== false)
                {
                    $post->author = null;
                }
            }
            
        }

        if (!$this->subredditStored_ByName($post->subreddit))
        {
            $this->AddSubreddit($post->subreddit_id, $post->subreddit);
        }

        $result = $stmt->bind_param("sssiisssss",
            $post->id,
            $post->author,
            $post->title,
            $post->created_utc,
            $post->score,
            $post->permalink,
            $post->link,
            $post->subreddit_id,
            $post->text,
            $post->text_html
        );

        

        if (false === $result)
        {
            SQL_Exc($this->connection);
        }

        if (false === $stmt->execute()) {
            if (strpos($stmt->error, 'a foreign key constraint fails') !== false
            &&
            strpos($stmt->error, 'FOREIGN KEY (`subreddit_id`) REFERENCES `Subreddits` (`id`)') !== false)
            {
                $this->AddSubreddit($post->subreddit_id, $post->subreddit);
                if (false === $stmt->execute())
                {

                }
                else
                {
                    return;
                }
            }
            WriteDump("Error: ", $stmt->error);
            WriteDump("State: ", $this->connection->sqlstate);
            WriteDump("Error Number: ", $this->connection->errno);
            WriteDump("Query: ", $query);
            WriteDump("Post: ", $post);
            SQL_Exc($stmt);
        }
    }

    function AddPosts($posts)
    {
        $ids = array_column($posts, "id");
        $ids = array_diff($ids, $this->PostsStored_ByID($ids));
        $posts = array_filter($posts, function($post) use ($ids) {
            return in_array($post->id, $ids);
        });
        foreach ($posts as $post)
        {
            $this->AddPost($post);
        }
    }

    function PostStoredByID($id)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->DatabaseName
                . "." . $this->schema->PostsName . " WHERE id = ?"
            ) or Exc($this->connection->error_get_last);
        }
            

        $query->bind_param("s", $id);

        $query->execute() or Exc($this->connection->error_get_last);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function postsStored_ByID($ids)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $len = count($ids);

        if ($len <= 0) {
            return [];
        }

        $paramString = str_repeat("s", $len);
        $paramList = "(?";
        for ($i = 1; $i < $len; $i = $i + 1) {
            $paramList = $paramList . ", ?";
        }
        $paramList = $paramList . ")";

        $query = "
                SELECT id FROM " . $this->schema->PostsTable() . " WHERE id IN " . $paramList;
            

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            throw new Exception(htmlspecialchars($this->connection->error));
        }

        foreach ($ids as $key => &$value) {
            $params[] =& $value;
        }

        $rc = call_user_func_array(
            array($stmt, "bind_param"),
            array_merge(
                array($paramString),
                $params
            )
        );

        if (false === $rc) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        return array_column($result, 0);
    }

    function addSubreddit($id, $name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "REPLACE INTO " . $this->schema->SubredditsTable()
            . " (
                subreddit_id,
                subreddit_name
            ) VALUES (
                ?,
                ?
            )";
        
        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            SQL_Exc($this->connection);
        }

        if (false === $stmt->bind_param("ss",
            $id,
            $name)
        ) {
            SQL_Exc($stmt);
        }

        if (false === $stmt->execute()) {
            SQL_Exc($stmt);
        }
    }

    function subredditStored_ByName($name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->SubredditsTable()
                . " WHERE subreddit_name = ?"
            ) or Exc($this->connection->error_get_last);
        }
            

        $query->bind_param("s", $name);

        $query->execute() or Exc($this->connection->error_get_last);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function subredditsStored_ByName($names)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $len = count($names);

        if ($len <= 0) {
            return [];
        }

        $paramString = str_repeat("s", $len);
        $paramList = "(?";
        for ($i = 1; $i < $len; $i = $i + 1) {
            $paramList = $paramList . ", ?";
        }
        $paramList = $paramList . ")";

        $query = "
                SELECT subreddit_name FROM " . $this->schema->SubredditsTable() . " WHERE subreddit_name IN " . $paramList;
            

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            throw new Exception(htmlspecialchars($this->connection->error));
        }

        foreach ($names as $key => &$value) {
            $params[] =& $value;
        }

        $rc = call_user_func_array(
            array($stmt, "bind_param"),
            array_merge(
                array($paramString),
                $params
            )
        );

        if (false === $rc) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        return array_column($result, 0);
    }

    function addComment($Reddit, $comment)
    {
        if ($comment === null)
        {
            return;
        }

        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "REPLACE INTO " . $this->schema->CommentsTable()
            . " (
                id,
                author,
                text,
                text_html,
                parent_id,
                permalink,
                post_id,
                score
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )";
        
        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            SQL_Exc($this->connection);
        }

        $author_name = $comment->author;

        if ($author_name === '[deleted]')
        {
            $author_name = null;
        }

        if (false === $stmt->bind_param("sssssssi",
            $comment->id,
            $author_name,
            $comment->text,
            $comment->text_html,
            $comment->parent_id,
            $comment->permalink,
            $comment->post_id,
            $comment->score)
        ) {
            SQL_Exc($stmt);
        }

        if (false === $stmt->execute()) {
            SQL_Exc($stmt);
        }
    }

    function commentStoredByID($id)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->CommentsTable()
                . " WHERE id = ?"
            ) or SQL_Exc($this->connection);
        }
            

        $query->bind_param("s", $id);

        $query->execute() or SQL_Exc($this->connection);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function commentsStored_ByID($ids)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $len = count($ids);

        if ($len <= 0) {
            return [];
        }

        $paramString = str_repeat("s", $len);
        $paramList = "(?";
        for ($i = 1; $i < $len; $i = $i + 1) {
            $paramList = $paramList . ", ?";
        }
        $paramList = $paramList . ")";

        $query = "
                SELECT id FROM " . $this->schema->CommentsTable() . " WHERE id IN " . $paramList;
            

        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            throw new Exception(htmlspecialchars($this->connection->error));
        }

        foreach ($ids as $key => &$value) {
            $params[] =& $value;
        }

        $rc = call_user_func_array(
            array($stmt, "bind_param"),
            array_merge(
                array($paramString),
                $params
            )
        );

        if (false === $rc) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        if (false === $stmt->execute()) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        $result = $stmt->get_result()->fetch_all();

        if (false === $result) {
            throw new Exception(htmlspecialchars($stmt->error));
        }

        return array_column($result, 0);
    }

    function addCommentsListing($Reddit, $listing, $checkUsers = true, &$deletedUsers = [])
    {
        if ($checkUsers)
        {
            $userNames = $listing->getAuthors();
            
            $userNames = array_unique($userNames);
    
            $userNames = array_diff($userNames, $this->UsersStored_ByName($userNames));
    
            $users = [];
    
            foreach ($userNames as $name)
            {
                try
                {
                    array_push($users, $Reddit->GetUser($name));
                }
                catch (RedditAPIException $ex)
                {
                    if ($ex->errorNo === 404)
                    {
                        array_push($deletedUsers, $name);
                    }
                    else
                    {
                        throw $ex;
                    }
                }
                
            }
    
            $this->AddUsers($users);
        }
        

        // $ids = $listing->getIDs();
        // $ids = array_diff($ids, $this->commentsStored_ByID($ids));

        foreach ($listing->comments as $comment)
        {
            if ($comment === null)
            {
                continue;
            }
            if (!$this->CommentStoredByID($comment->id))
            {
                if (in_array($comment->author, $deletedUsers))
                {
                    $comment->author = null;
                }
                $this->AddComment($Reddit, $comment);
                if ($comment->replies != null)
                {
                    $this->AddCommentsListing($Reddit, $comment->replies, false, $deletedUsers);
                }
            }
        }

        //TODO: Retrieve and store "more" comments from the listing
    }








    function GetCommentByID($id, $include_replies = false)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT * FROM " . $this->schema->CommentsTable()
                . " JOIN "
                . $this->schema->UsersTable()
                . " ON " . $this->schema->CommentsTable("author")
                . " = " . $this->schema->UsersTable("user_name")
                . " JOIN " . $this->schema->PostsTable()
                . " ON " . $this->schema->PostsTable("id") . " = " . $this->schema->CommentsTable("post_id")
                . " JOIN " . $this->schema->SubredditsTable()
                . " ON " . $this->schema->SubredditsTable("subreddit_id") . " = "
                . $this->schema->PostsTable("subreddit_id")
                . " WHERE " . $this->schema->CommentsTable("id") . " = ?"
            ) or SQL_Exc($this->connection);
        }
            

        $query->bind_param("s", $id);

        $query->execute() or Exc($this->connection->error_get_last);
        $result = $query->get_result()->fetch_array();
        $comment = new Comment();
        $comment->id = $id;
        $comment->body = $result["text"];
        $comment->body_html = $result["text_html"];
        $comment->author = $result["user_name"];
        $comment->post_id = $result["post_id"];
        $comment->score = $result["score"];
        $comment->subreddit_id = $result["subreddit_id"];
        $comment->subreddit = $result["subreddit_name"];

        //TODO...

        return $comment;
    }

    public function siteUserExists($name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        static $query = null;
        if ($query === null)
        {
            $query = $this->connection->prepare(
                "SELECT COUNT(username) FROM " . $this->schema->SiteUsersTable() . " WHERE username = ?"
            )  or SQL_Exc($this->connection);
        }


        $query->bind_param("s", $name);
        $query->execute() or SQL_Exc($this->connection);
        $result = $query->get_result()->fetch_array();

        return ($result[0] > 0);
    }

    public function createSiteUser($username, $hash)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        static $query = null;
        if ($query === null)
        {
            $query = $this->connection->prepare(
                "INSERT INTO " . $this->schema->SiteUsersTable() . " (username, auth_key) VALUES (?, ?);"
            )  or SQL_Exc($this->connection);
        }


        $query->bind_param("ss", $username, $hash);
        $query->execute() or SQL_Exc($this->connection);

        $id = $this->getSiteUserID($username);

        static $permQuery = null;
        if ($permQuery == null)
        {
            $permQuery = $this->connection->prepare(
                "INSERT INTO " . $this->schema->PermissionsTable() . " (user_id) VALUES (?);"
            ) or SQL_Exc($this->connection);
        }

        $permQuery->bind_param("i", $id);
        $permQuery->execute() or SQL_Exc($this->connection);
    }

    public function getSiteUserID($username)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        static $query = null;
        if ($query == null)
        {
            $query = $this->connection->prepare(
                'SELECT id FROM ' . $this->schema->SiteUsersTable() . ' WHERE username=?'
            ) or SQL_Exc($this->connection);
        }

        $query->bind_param('s', $username);
        $query->execute() or SQL_Exc($this->connection);
        $res = $query->get_result()->fetch_array();
        return (int)$res[0];
    }



    public function startTransaction()
    {
        $this->connection->begin_transaction();
    }

    public function endTransaction()
    {
        try
        {
            $this->connection->commit();
        }
        catch (Exception $ex)
        {
            $this->connection->rollback();
            throw $ex;
        }
        
    }

    public function rollback()
    {
        $this->connection->rollback();
    }

    public function disableFK()
    {
        $this->query('SET FOREIGN_KEY_CHECKS=0');
    }

    public function enableFK()
    {
        $this->query('SET FOREIGN_KEY_CHECKS=1');
    }
}










    