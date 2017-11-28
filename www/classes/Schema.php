<?php

require_once(__DIR__ . '/../' . 'classes/SiteUser.php');

class DBSchema
{
    public $DatabaseName = "RedditDB";
    public $PostsName = "Posts";
    public $CommentsName = "Comments";
    public $UsersName = "Users";
    public $SubredditsName = "Subreddits";
    // public $PostsCommentsName = "PostsComments";
    public $SiteUsersName = "SiteUsers";
    public $WatchedPostsName = "WatchedPosts";
    public $WatchedCommentsName = "WatchedComments";
    public $WatchedSubredditsName = "WatchedSubreddits";
    public $WatchedUsersName = "WatchedUsers";
    public $QueueName = "Queue";
        
    public function Initialize($client)
    {
        $DB_create_query = "CREATE DATABASE IF NOT EXISTS " . $this->DatabaseName;
        $client->query($DB_create_query) or SQL_Exc($client);

        $users_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->UsersName .
        " (
            user_name VARCHAR(20) PRIMARY KEY,
            id VARCHAR(128) DEFAULT NULL,
            utc_created TIMESTAMP,
            link_score INT,
            comment_score INT,

            UNIQUE(id)
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
                author VARCHAR(20),
                title TEXT NOT NULL,
                creation_timestamp TIMESTAMP NOT NULL,
                score INT NOT NULL,
                permalink VARCHAR(2083) NOT NULL,
                link VARCHAR(2083),
                subreddit_id VARCHAR(128) NOT NULL,
                text TEXT,
                text_html TEXT,

                FOREIGN KEY (author) REFERENCES " . $this->UsersName . "(user_name),
                FOREIGN KEY (subreddit_id) REFERENCES " . $this->SubredditsName . "(subreddit_id)
            )";
        $client->query($posts_create_query) or Exc($client->error_get_last);



        $comments_create_query = "CREATE TABLE IF NOT EXISTS " . $this->DatabaseName .
        "." . $this->CommentsName .
        " (
                id VARCHAR(128) PRIMARY KEY,
                author VARCHAR(20),
                text TEXT,
                text_html TEXT,
                parent_id VARCHAR(128),
                permalink VARCHAR(2083),
                post_id VARCHAR(128) NOT NULL,
                score INT,

                FOREIGN KEY (author) REFERENCES " . $this->UsersName . " (user_name),
                FOREIGN KEY (parent_id) REFERENCES " . $this->CommentsName . " (id),
                FOREIGN KEY (post_id) REFERENCES " . $this->PostsName . " (id)
            )";
        $client->query($comments_create_query) or Exc($client->error_get_last);
        
        $site_users_create_query = "CREATE TABLE IF NOT EXISTS " . $this->SiteUsersTable() .
        " (
                id INT PRIMARY KEY AUTO_INCREMENT,
        		username varchar(100) UNIQUE NOT NULL,
                auth_key TEXT NOT NULL,
                perm_backup BOOL NOT NULL DEFAULT FALSE,
                perm_restore BOOL NOT NULL DEFAULT FALSE,
                perm_edit BOOL NOT NULL DEFAULT FALSE,
                perm_manage_users BOOL NOT NULL DEFAULT FALSE

        )";
        $client->query($site_users_create_query) or Exc($client->error_get_last);
        
        $watched_posts_create_query = "CREATE TABLE IF NOT EXISTS " . $this->WatchedPostsTable() .
        " (
        		user_id INT NOT NULL,
        		post_id VARCHAR(128) NOT NULL,
                
                PRIMARY KEY(user_id, post_id),
        		FOREIGN KEY (user_id) REFERENCES " . $this->SiteUsersTable() . " (id),
        		FOREIGN KEY (post_id) REFERENCES " . $this->PostsTable() . " (id)
         )";
         $client->query($watched_posts_create_query) or Exc($client->error_get_last);
         
         $watched_comments_create_query = "CREATE TABLE IF NOT EXISTS " . $this->WatchedCommentsTable() .
         " (
        		user_id INT NOT NULL,
        		comment_id VARCHAR(128) NOT NULL,
                
                PRIMARY KEY(user_id, comment_id),
        		FOREIGN KEY (user_id) REFERENCES " . $this->SiteUsersTable() . " (id),
        		FOREIGN KEY (comment_id) REFERENCES " . $this->CommentsTable() . " (id)
         )";
         $client->query($watched_comments_create_query) or Exc($client->error_get_last);
         
         $watched_subreddits_create_query = "CREATE TABLE IF NOT EXISTS " . $this->WatchedSubredditsTable() .
         " (
        		user_id INT NOT NULL,
        		subreddit_id VARCHAR(128) NOT NULL,
                
                PRIMARY KEY(user_id, subreddit_id),
        		FOREIGN KEY (user_id) REFERENCES " . $this->SiteUsersTable() . " (id),
        		FOREIGN KEY (subreddit_id) REFERENCES " . $this->SubredditsTable() . " (subreddit_id)
         )";
         $client->query($watched_subreddits_create_query) or Exc($client->error_get_last);
         
         $watched_users_create_query = "CREATE TABLE IF NOT EXISTS " . $this->WatchedPostsTable() .
         " (
        		user_id INT NOT NULL,
        		reddit_user VARCHAR(20) NOT NULL,
                
                PRIMARY KEY(user_id, reddit_user),
        		FOREIGN KEY (user_id) REFERENCES " . $this->SiteUsersTable() . " (id),
        		FOREIGN KEY (reddit_user) REFERENCES " . $this->UsersTable() . " (user_name)
         )";
         $client->query($watched_users_create_query) or Exc($client->error_get_last);
         
         $queue_create_query = "CREATE TABLE IF NOT EXISTS " . $this->QueueTable() . " (
         	timestamp TIMESTAMP,
         	type INT,
         	request TEXT
         )";
         $client->query($queue_create_query) or Exc($client->error_get_last);
        
        $addUserProc = $this->createProc_AddUser($client);
        assert($addUserproc);
        
        //Add Admin account if missing
        if ((int)$client->queryToArray('SELECT COUNT(*) "X" FROM ' . $this->SiteUsersTable() . " WHERE username = 'Admin';")[0]['X'] <= 0)
        {
            $this->AddAdminAccount($client);
        }

        //Ensure Admin has full permissions
        $client->query(
            "UPDATE " . $this->SiteUsersTable() . " SET perm_backup=TRUE, perm_restore=TRUE, perm_edit=TRUE, perm_manage_users=TRUE WHERE username='Admin';"
        ) or Exc($client->error_get_last);
    }

    public function CommentsTable($column = null)
    {
        $str = $this->DatabaseName . "." . $this->CommentsName;
        if ($column != null)
        {
            $str = $str . "." . $column;
        }
        return $str;
    }

    public function PostsTable($column = null)
    {
        $str = $this->DatabaseName . "." . $this->PostsName;
        if ($column != null)
        {
            $str = $str . "." . $column;
        }
        return $str;
    }

    public function UsersTable($column = null)
    {
        $str = $this->DatabaseName . "." . $this->UsersName;
        if ($column != null)
        {
            $str = $str . "." . $column;
        }
        return $str;
    }

    public function SubredditsTable($column = null)
    {
        $str = $this->DatabaseName . "." . $this->SubredditsName;
        if ($column != null)
        {
            $str = $str . "." . $column;
        }
        return $str;
    }
    
    public function SiteUsersTable($column = null)
    {
        $str = $this->DatabaseName . "." . $this->SiteUsersName;
        if ($column != null)
        {
            $str = $str . "." . $column;
        }
        return $str;
    }

    public function WatchedPostsTable($col = null)
    {
        $str = $this->Database($this->WatchedPostsName);
        if ($col != null)
        {
            return $str . "." . $col;
        }
        return $str;
    }

    public function WatchedCommentsTable($col = null)
    {
        $str = $this->Database($this->WatchedCommentsName);
        if ($col != null)
        {
            return $str . "." . $col;
        }
        return $str;
    }

    public function WatchedSubredditsTable($col = null)
    {
        $str = $this->Database($this->WatchedSubredditsName);
        if ($col != null)
        {
            return $str . "." . $col;
        }
        return $str;
    }

    public function WatchedUsersTable($col = null)
    {
        $str = $this->Database($this->WatchedUsersName);
        if ($col != null)
        {
            return $str . "." . $col;
        }
        return $str;
    }
    
    public function QueueTable($col = null)
    {
    	$str = $this->Database($this->QueueName);
    	if ($col != null)
    	{
    		$str = $str . "." . $col;
    	}
    	return $str;
    }

    public function Database($table = null)
    {
        $str = $this->DatabaseName;
        if ($table !== null)
        {
            $str = $str . "." . $table;
        }
        return $str;
    }

    public function createProc_AddUser($client)
    {
        $query_drop = 'DROP PROCEDURE IF EXISTS '
            . $this->Database('AddUser');
        $client->query($query_drop);

        $query = 'CREATE PROCEDURE  
            ' . $this->Database('AddUser') . ' (
                    name TEXT,
                    uid TEXT,
                    created INT,
                    lnk_scr INT,
                    comment_scr INT
                )
            BEGIN
                INSERT INTO ' . $this->UsersTable() . '
                (id, user_name, utc_created, link_score, comment_score)
                VALUES
                (uid, name, FROM_UNIXTIME(created), lnk_scr, comment_scr)
                ON DUPLICATE KEY UPDATE
                    id = IF(uid <> NULL, uid, id),
                    utc_created = IF(created <> NULL, FROM_UNIXTIME(created), utc_created),
                    link_score = IF(lnk_scr <> NULL, lnk_scr, link_score),
                    comment_score = IF(comment_scr <> NULL, comment_scr, comment_score)
                ;
            END';
        $res = $client->query($query);
        return $res === true;
    }


    public function drop($client)
    {
        $drop_query = "DROP DATABASE IF EXISTS " . $this->DatabaseName;
        $client->query($drop_query) or Exc($client->error_get_last);
    }

    private function addAdminAccount($client)
    {
        $hashed = SiteUser::HashPassword(DEFAULT_ADMIN_PASS);
        $client->CreateSiteUser('Admin', $hashed);
    }
}

?>