<?php

class RedditAPIException extends Exception
{
    private $__msg;
    private $__num;
    private $__cat;

    function __construct($category, $errorno, $errmsg)
    {
        $this->__msg = strtolower($errmsg);
        $this->__num = $errorno;
        $this->__cat = strtolower($category);
    }

    function __get($name)
    {
        if (strtoupper($name) == "MSG") {
            return $this->__msg;
        } elseif (strtoupper($name) == "ERRORNO") {
            return $this->__num;
        } elseif (strtoupper($name) == "CATEGORY") {
            return $this->__cat;
        }
    }
}
    
class Post
{
    public $author;
    public $title;
    public $id;
    public $isSelf;
    public $permalink;
    public $score;
    public $link;
    public $subreddit;
    public $subreddit_id;
    public $comments;
    public $utc_timestamp;

    function __construct()
    {
        $this->isSelf = false;
    }

    function deserialize($json)
    {
        $this->author = $json["author"];
        $this->title = $json["title"];
        $this->id = $json["id"];
        $this->permalink = $json["permalink"];
        $this->score = $json["score"];
        $this->link = $json["url"];
        $this->subreddit = $json["subreddit"];
        $this->subreddit_id = $json["subreddit_id"];
        $this->utc_timestamp = $json["created_utc"];
    }

    static function FromJson($json)
    {
        $post = new Post();
        $post->deserialize($json);
        return $post;
    }

    function getComments()
    {
        if ($this->comments != null) {
            return;
        }
            

        $reddit = new Reddit();
        $this->comments = $reddit->GetComments($this->id);
    }
}

class SelfPost extends Post
{
    public $text;
    public $text_html;

    function __construct()
    {
        Post::__construct();
        $this->isSelf = true;
    }

    function deserialize($json)
    {
        parent::deserialize($json);
        $this->text = $json["selftext"];
        $this->text_html = $json["selftext_html"];
    }
}


class Comment
{
    public $subreddit;
    public $subreddit_id;
    public $post_id;
    public $replies;
    public $id;
    public $score;
    public $body;
    public $body_html;
    public $author;
    public $parent_id;
    public $edited;
    public $utc_timestamp;
    public $permalink;

    function __construct()
    {
        // $this->replies = array();
    }

    function deserialize($json)
    {

        $json = $json["data"];
        if (!array_key_exists("subreddit_id", $json))
        {
            throw new Exception();
        }

        $this->subreddit = $json["subreddit"];
        $this->subreddit_id = $json["subreddit_id"];
        $this->post_id = substr($json["link_id"], 3);
        if ($json["replies"] != NULL)
        {
            $this->replies = CommentListing::FromJson($json["replies"]["data"]["children"]);
        }
        else
        {
            $this->replies = null;
        }
        $this->id = $json["id"];
        $this->score = $json["score"];
        $this->body = $json["body"];
        $this->body_html = $json["body_html"];
        $this->author = $json["author"];
        if ("t3_" != substr($json["parent_id"], 0, 3)) {
            $this->parent_id = substr($json["parent_id"], 3);
        } else {
            $this->parent_id = null;
        }
        $this->edited = $json["edited"];
        $this->utc_timestamp = $json["created_utc"];
        $this->permalink = $json["permalink"];
    }

    static function FromJson($json)
    {
        $comment = new Comment();
        $comment->deserialize($json);
        return $comment;
    }
}

class CommentListing
{
    public $comments;
    public $more;

    function __construct()
    {
        $this->more = array();
        $this->comments = array();
    }

    static function FromJson($json)
    {
        $listing = new CommentListing();
        foreach ($json as $jComment)
        {
            if ($jComment["kind"] === "t1")
            {
                $comment = new Comment();
                $comment->deserialize($jComment);
                array_push($listing->comments, $comment);
            }
            else if ($jComment["kind"] === "more")
            {
                // WriteDump($jComment);
                // throw new Exception("Found a 'more' object!");
                $listing->more = array_merge($listing->more, $jComment["data"]["children"]);
            }
        }

        return $listing;
    }
}


function DeserializePost($json)
{
    if ($json["is_self"]) {
        $post = new SelfPost();
    } else {
        $post = new Post();
    }
    $post->deserialize($json);
    return $post;
}


class Reddit
{
    private $curl;
    private $redditURL = "https://www.reddit.com";

    private function GetJSON($path, $queryStr = '')
    {
        $URL = $this->redditURL . $path . ".json";
        if ($queryStr) {
            $URL = $URL . "?" . $queryStr;
        }
        curl_setopt($this->curl, CURLOPT_URL, $URL);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($this->curl);
        return json_decode($result, true);
    }

    function __construct()
    {
        $this->curl = curl_init();
    }

    function GetSubredditListingJSON($subreddit, $limit = 0)
    {
        if ($limit > 0)
        {
            return $this->GetJSON("/r/" . $subreddit, "limit=" . $limit);
        }
        else
        {
            return $this->GetJSON("/r/" . $subreddit);
        }
        
    }

    function GetSubredditListing($subreddit, $limit = 0)
    {
        if ($limit > 0)
        {
            $json = $this->GetJSON("/r/" . $subreddit, "limit=" . $limit)["data"]["children"];
        }
        else
        {
            $json = $this->GetJSON("/r/" . $subreddit)["data"]["children"];
        }
        
        $posts = array();
        $i = 0;
        foreach ($json as $jsonpost_container) {
            $jsonPost = $jsonpost_container["data"];
            if ($jsonPost["over_18"])
            {
                continue;
            }
            $post = &$posts[$i];
            $post = DeserializePost($jsonPost);
            $i = $i + 1;
        }
        return $posts;
    }

    function GetCommentsJSON($post_id, $count = 0)
    {
        return $this->GetJSON("/comments/" . $post_id . "/")[1];
    }

    function GetComments($post_id, $count = 0)
    {
        $rawJson = $this->GetCommentsJSON($post_id, $count);
        if ($rawJson === null)
        {
            throw new RedditAPIException("post", 0, "unexpected null rawJson");
        }
        $json = $rawJson["data"]["children"];
        if ($json === null)
        {
            throw new RedditAPIException("post", 0, "unexpected null json");
        }


        $comments = new CommentListing();
        $comments = CommentListing::FromJson($json);

        return $comments;
    }

    function GetUserInfoJSON($name)
    {
        return $this->GetJSON("/user/" . $name . "/about");
    }

    function GetUserInfo($name, &$id, &$utc_timestamp, &$link_score, &$comment_score)
    {
        if ($name === "[deleted]")
        {
            $id = null;
            $utc_timestamp = null;
            $link_score = null;
            $comment_score = null;
            return;
        }
        $json = $this->GetUserInfoJSON($name);

        if (array_key_exists("message", $json)) {
            if (array_key_exists("error", $json)) {
                if ($json["error"] == 404) {
                    throw new RedditAPIException("user", 404, "user '" . $name . "' does not exist");
                } else {
                    throw new RedditAPIException("user", $json["error"], $json["message"]);
                }
            }
        }

        $json = $json["data"];
        $id = $json["id"];
        $utc_timestamp = $json["created_utc"];
        $link_score = $json["link_karma"];
        $comment_score = $json["comment_karma"];

        //TODO
    }

    function GetUserPostsJSON($name, $sorting = "new", $count = 10)
    {
        $json = $this->GetJSON("/user/" . $name . "/submitted", "sort=" . $sorting . "&limit=" . $count);
        if (array_key_exists("message", $json)) {
            if (array_key_exists("error", $json)) {
                if ($json["error"] == 404) {
                    throw new RedditAPIException("user", 404, "user does not exist");
                } else {
                    throw new RedditAPIException("user", $json["error"], $json["message"]);
                }
            } else {
                //TODO
            }
        }
        return $json;
    }

    function GetUserPosts($name, $sorting = "new", $count = 10)
    {
        $json = $this->GetUserPostsJSON($name, $sorting, $count);
    }

    function GetUserCommentsJSON($name, $sorting = "new", $count = 10)
    {
        $json = $this->GetJSON("/user/" . $name . "/comments", "sort=" . $sorting . "&limit=" . $count);
        if (array_key_exists("message", $json)) {
            if (array_key_exists("error", $json)) {
                if ($json["error"] == 404) {
                    throw new RedditAPIException("user", 404, "user does not exist");
                } else {
                    throw new RedditAPIException("user", $json["error"], $json["message"]);
                }
            } else {
                //TODO
            }
        }
        return $json;
    }

    function GetUserComments($name, $sorting = "new", $count = 10)
    {
        $json = $this->GetUserCommentsJSON($name, $sorting, $count);
    }

    function GetComment($postID, $commentID)
    {
        $json = $this->GetJSON("/comments/" . $postID . "/" . $commentID . "/", "limit=1")[1];
        $json = $json["data"]["children"][0];

        return Comment::FromJson($json);
    }
}
