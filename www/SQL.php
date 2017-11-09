<?php

require_once('config.php');

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

    function InitializeSchema()
    {
        return $this->schema->Initialize($this);
    }

    function DropSchema()
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

    function AddUser($id, $name, $utc_created, $link_score, $comment_score)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query =
            "INSERT INTO " . $this->schema->DatabaseName . "." .
            $this->schema->UsersName . " (
                id,
                user_name,
                utc_created,
                link_score,
                comment_score
            ) VALUES (
                ?,
                ?,
                FROM_UNIXTIME(?),
                ?,
                ?
            )";
        
        $stmt = $this->connection->prepare($query);

        if (false === $stmt) {
            SQL_Exc($this->connection);
        }

        if (false === $stmt->bind_param("ssiii",
            $id,
            $name,
            $utc_created,
            $link_score,
            $comment_score)
        ) {
            SQL_Exc($stmt);
        }

        if (false === $stmt->execute()) {
            SQL_Exc($stmt);
        }
    }

    function AddPost($Reddit, $post)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "INSERT INTO " . $this->schema->DatabaseName . "." .
            $this->schema->PostsName . " (
                id,
                author_id,
                title,
                creation_timestamp,
                score,
                isSelf,
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
                ?,
                ?
            )";
        
        $stmt = $this->connection->prepare($query);

        $author_id = null;
        if ($post->author != null)
        {
            $author_id = $this->GetUserID($post->author);
            if ($author_id === null)
            {
                $Reddit->GetUserInfo($post->author, $author_id, $author_utc_created, $author_link_score, $author_comment_score);
                $this->AddUser($author_id, $post->author, $author_utc_created, $author_link_score, $author_comment_score);
            }
        }

        if (!$this->SubredditStoredByName($post->subreddit))
        {
            $this->AddSubreddit($post->subreddit_id, $post->subreddit);
        }

        if ($post->isSelf)
        {
            $is_self = 1;

            $result = $stmt->bind_param("sssiiisssss",
                $post->id,
                $author_id,
                $post->title,
                $post->utc_timestamp,
                $post->score,
                $is_self,
                $post->permalink,
                $post->link,
                $post->subreddit_id,
                $post->text,
                $post->text_html
            );
        }
        else
        {
            $text = null;
            $html_text = null;
            $is_self = 0;

            $result = $stmt->bind_param("sssiiisssss",
                $post->id,
                $author_id,
                $post->title,
                $post->utc_timestamp,
                $post->score,
                $is_self,
                $post->permalink,
                $post->link,
                $post->subreddit_id,
                $text,
                $html_text
            );
        }

        

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
            SQL_Exc($stmt);
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

    function AddSubreddit($id, $name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "INSERT INTO " . $this->schema->DatabaseName . "." .
            $this->schema->SubredditsName . " (
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

    function SubredditStoredByName($name)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->DatabaseName
                . "." . $this->schema->SubredditsName . " WHERE subreddit_name = ?"
            ) or Exc($this->connection->error_get_last);
        }
            

        $query->bind_param("s", $name);

        $query->execute() or Exc($this->connection->error_get_last);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function AddComment($comment)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }

        $query = 
            "INSERT INTO " . $this->schema->DatabaseName . "." .
            $this->schema->CommentsName . " (
                id,
                author_id,
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

        $author_id = $this->GetUserID($comment->author);
        if ($author_id === null)
        {
            $Reddit = new Reddit();
            $Reddit->GetUserInfo($comment->author, $author_id, $author_utc_created, $author_link_score, $author_comment_score);
            if ($author_id !== null)
            {
                $this->AddUser($author_id, $comment->author, $author_utc_created, $author_link_score, $author_comment_score);
            }
            
        }

        if (false === $stmt->bind_param("sssssssi",
            $comment->id,
            $author_id,
            $comment->body,
            $comment->body_html,
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

    function CommentStoredByID($id)
    {
        if (!$this->isOpen()) {
            Exc("Connection has not been opened!");
        }
            
        static $query = null;
        if ($query == null) {
            $query = $this->connection->prepare(
                "SELECT COUNT(*) FROM " . $this->schema->DatabaseName
                . "." . $this->schema->CommentsName . " WHERE id = ?"
            ) or SQL_Exc($this->connection);
        }
            

        $query->bind_param("s", $id);

        $query->execute() or SQL_Exc($this->connection);
        $result = $query->get_result()->fetch_array()[0];
        return $result > 0;
    }

    function AddCommentsListing($listing)
    {
        foreach ($listing->comments as $comment)
        {
            if (!$this->CommentStoredByID($comment->id))
            {
                $this->AddComment($comment);
                if ($comment->replies != null)
                {
                    $this->AddCommentsListing($comment->replies);
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
                "SELECT * FROM " . $this->schema->DatabaseName
                . "." . $this->schema->CommentsName . " JOIN "
                . $this->schema->DatabaseName . "." . $this->schema->UsersName
                . " ON " . $this->schema->DatabaseName
                . "." . $this->schema->CommentsName . ".author_id = "
                . $this->schema->DatabaseName . "." . $this->schema->UsersName
                . ".id "
                . " JOIN " . $this->schema->DatabaseName . "." . $this->schema->PostsName
                . " ON " . $this->schema->PostsName . ".id = " . $this->schema->CommentsName
                . ".post_id"
                . " JOIN " . $this->schema->DatabaseName . "." . $this->schema->SubredditsName
                . " ON " . $this->schema->SubredditsName . "." . "subreddit_id = "
                . $this->schema->PostsName . ".subreddit_id"
                . " WHERE " . $this->schema->DatabaseName
                . "." . $this->schema->CommentsName . ".id = ?"
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
}









class DBSchema
{
    public $DatabaseName = "RedditDB";
    public $PostsName = "Posts";
    public $CommentsName = "Comments";
    public $UsersName = "Users";
    public $SubredditsName = "Subreddits";
    // public $PostsCommentsName = "PostsComments";
        
    public function Initialize($client)
    {
        $DB_create_query = "CREATE DATABASE IF NOT EXISTS " . $this->DatabaseName;
        $client->query($DB_create_query) or SQL_Exc($client);

        $users_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->UsersName .
        " (
            id VARCHAR(128) PRIMARY KEY,
            user_name VARCHAR(20) NOT NULL,
            utc_created TIMESTAMP NOT NULL,
            link_score INT NOT NULL,
            comment_score INT NOT NULL,

            UNIQUE(user_name)
        )";
        $client->query($users_create_query);

        $subreddits_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->SubredditsName .
        " (subreddit_id VARCHAR(128) PRIMARY KEY, subreddit_name TEXT NOT NULL)";
        $client->query($subreddits_create_query) or SQL_Exc($client);


        $posts_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->PostsName .
        " (
                id VARCHAR(128) PRIMARY KEY,
                author_id VARCHAR(128),
                title TEXT NOT NULL,
                creation_timestamp TIMESTAMP NOT NULL,
                score INT NOT NULL,
                isSelf BOOL NOT NULL,
                permalink VARCHAR(2083) NOT NULL,
                link VARCHAR(2083),
                subreddit_id VARCHAR(128) NOT NULL,
                text TEXT,
                text_html TEXT,

                FOREIGN KEY (author_id) REFERENCES " . $this->UsersName . "(id),
                FOREIGN KEY (subreddit_id) REFERENCES " . $this->SubredditsName . "(subreddit_id)
            )";
        $client->query($posts_create_query) or Exc($client->error_get_last);



        $comments_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->CommentsName .
        " (
                id VARCHAR(128) PRIMARY KEY,
                author_id VARCHAR(128),
                text TEXT NOT NULL,
                text_html TEXT NOT NULL,
                parent_id VARCHAR(128),
                permalink VARCHAR(2083) NOT NULL,
                post_id VARCHAR(128) NOT NULL,
                score INT,

                FOREIGN KEY (author_id) REFERENCES " . $this->UsersName . " (id),
                FOREIGN KEY (parent_id) REFERENCES " . $this->CommentsName . " (id),
                FOREIGN KEY (post_id) REFERENCES " . $this->PostsName . " (id)
            )";
        $client->query($comments_create_query) or Exc($client->error_get_last);

        // $posts_comments_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        // "." . $this->PostsCommentsName .
        // " (
        //         post_id VARCHAR(128) NOT NULL,
        //         comment_id VARCHAR(128) NOT NULL,

        //         PRIMARY KEY (post_id, comment_id),

        //         FOREIGN KEY (post_id) REFERENCES " . $this->PostsName . "(id),
        //         FOREIGN KEY (comment_id) REFERENCES " . $this->CommentsName . "(id)
        //     )";
        // $client->query($posts_comments_create_query) or Exc($client->error_get_last);
    }

    public function drop($client)
    {
        $drop_query = "DROP DATABASE IF EXISTS " . $this->DatabaseName;
        $client->query($drop_query) or Exc($client->error_get_last);
    }
}
