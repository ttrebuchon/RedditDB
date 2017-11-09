<?php

    include("Reddit.php");

function testWrite($filepath, $data)
{
    $file = fopen($filepath, "a+") or die("Couldn't open file!!");

    fwrite($file, $data);

    fclose($file);
}

function outputJS($code)
{
    return "<script type=\"text/javascript\">" . $code . "</script>";
}

    $testJS = "console.log('Hello, world!');";

function WriteLine($line = "")
{
    echo '<pre>' . $line . '</pre>';
    //echo $line . "\n<br />\n";
}

function WriteDump($label, $var = null)
{
    $line = "";
    if ($var == null && gettype($label) != "string") {
        $line = var_export($label, true);
    } else {
        $line = $label . var_export($var, true);
        ;
    }


    WriteLine($line);
}


function echoAndWrite($filepath, $data)
{
    testWrite($filepath, $data);
    echo $data;
}

function echoAndWriteLine($filepath, $data)
{
    testWrite($filepath, $data);
    WriteLine($data);
}

    

    echoAndWrite("Hello.txt", outputJS($testJS) . "\n");

    echoAndWriteLine("Hello.txt", "Hello, world!");


class Foo
{
    private $curl;

    function __construct()
    {
        $this->curl = curl_init();
    }

    function GetJSON($path)
    {
        return json_decode($this->GetJSONString($path), true);
    }

    function GetJSONString($path)
    {
        $URL = "https://www.reddit.com/" . $path . ".json";
        //$this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $URL);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($this->curl);
        //curl_close($this->curl);
        return $result;
    }
}


    $foo_inst = new Foo();
    $JSON = $foo_inst->GetJSON("r/pics");
    WriteLine("JSON Retrieved!");
    WriteLine($JSON["kind"]);

    $JSON_STR = $foo_inst->GetJSONString("r/gaming");
    echo outputJS("var x = " . $JSON_STR . ";");
    echo outputJS("console.log(x);");

    $reddit = new Reddit() or die("ALSO DYING");

    //WriteLine(var_dump($reddit));

    $pics_json = $reddit->GetSubredditListingJSON("pics");

    //WriteLine(var_dump($pics_json));

    WriteLine();
    WriteLine();
    WriteLine();
    WriteLine();

    $pics = $reddit->GetSubredditListing("pics");
    //WriteLine(var_dump($pics));


    WriteLine();
    WriteLine();
    WriteLine();
    WriteLine();

    $nonself_post = null;

    $posts = $JSON["data"]["children"];

for ($i = 0; $i < count($posts); $i = $i + 1) {
    if (!$posts[$i]["data"]["is_self"]) {
        $nonself_post = $posts[$i]["data"];
        break;
    }
}

    // echo outputJS("var y = " . json_encode($posts[0]["data"]) . ";");
    echo outputJS("var y = " . json_encode($nonself_post) . ";");
    echo outputJS("console.log(y);");

    WriteLine("Done!");


    //WriteLine(var_dump($reddit->GetSubredditListing("pics")[0]));

    echo "Retrieving...";
    WriteLine();
    WriteLine();

    //WriteLine(var_dump($pics[0]));

    $pics[0]->getComments();
    $comments = $pics[0]->comments;

    //echo gettype($comments);
    WriteLine(var_dump($comments));

    $client = new RedditSQLClient(DBHOST);

try {
    // $schema = new DBSchema();
    // $schema->drop($client);
    // $schema->Initialize($client);

    //$client->DropSchema();
    $client->InitializeSchema();

    echo "Exists? ";
    WriteDump($client->UserStored_ByID(1));

    WriteDump($client->UsersStored_ByID([1, 2, 3, 4]));

    WriteLine();

    WriteDump($client->UsersStored_ByID([]));
        
    WriteLine("Schema Initialized");
} catch (Exception $e) {
    WriteLine("Caught Exception: ");
    WriteDump($e);
}

$gaming = $reddit->GetSubredditListing('gaming');
//$gaming_id = $reddit->GetSubredditID('gaming');

//$client->AddSubreddit($gaming[0]->subreddit_id, $gaming[0]->subreddit);

foreach ($gaming as $post) {
    //$id; $utc_created; $link_score; $comment_score;
    
    if (!$client->UserStored_ByName($post->author))
    {
        $reddit->GetUserInfo($post->author, $id, $utc_created, $link_score, $comment_score);
        WriteLine($id);
        $client->AddUser($id, $post->author, $utc_created, $link_score, $comment_score);
    }

    if (!$client->PostStoredByID($post->id))
    {
        $client->AddPost($reddit, $post);
    }

    
}

$names = array_column($gaming, "author");
sort($names);

$names_len = count($names);

$namesInDB = $client->UsersStored_ByName($names);

WriteDump($names);
WriteDump($namesInDB);

WriteLine("Names Difference:");
$names_inter = array_intersect($names, $namesInDB);
WriteDump($names_inter);

$names_excluded = array_diff(($names + $namesInDB), $names_inter);

WriteLine();
WriteLine("Excluded: ");
WriteDump($names_excluded);

try {
    $reddit->GetUserPostsJSON("aufdbaufuwfufauawfgu");
} catch (RedditAPIException $e) {
    WriteLine("Caught an exception...");
    if ($e->category != "user" || $e->errorno != 404) {
        throw $e;
    } else {
        WriteLine("404 Successfully Caught!");
    }
}


//WriteDump($reddit->GetComments("770smp"));


$base_post = $gaming[0];

$comments_q = $base_post->GetComments();

$authors = array();

$count = 100;
$index = 0;
while ($index < $count) {
    if (count($comments_q) <= 0) {
        break;
    } else {
    }

    while (count($comments_q) > 0) {
        $comment = $comments_q[0];
        $comments_q = array_shift($comments_q);

        array_push($authors, $comment->author);
    }

    $already_present = $client->UsersStored_ByName($authors);
    $authors = array_diff($authors, $already_present);

    foreach ($authors as $author)
    {
        $id = "";
        $utc_created = $link_score = $comment_score = 0;
        $reddit->GetUserInfo($author, $id, $utc_created, $link_score, $comment_score);
        $client->AddUser($id, $post->author, $utc_created, $link_score, $comment_score);
        $index = $index + 1;
    }

    $authors = array();

    

}

$someComment = $reddit->GetComment("5ar029", "d9ikjkg");

WriteDump("Some Comment: ", $someComment);

$all = $reddit->GetSubredditListing("all", 5);
foreach ($all as $post)
{
    if (!$client->PostStoredByID($post->id))
    {
        $client->AddPost($reddit, $post);
    }
}

$all_subs = array();

foreach ($all as $post)
{
    array_push($all_subs, $post->subreddit);
}

$all_subs = array_unique($all_subs);

$authors = array();



foreach ($all_subs as $sub)
{
    $posts = $reddit->GetSubredditListing($sub, 3);
    foreach ($posts as $post)
    {
        if (!$client->PostStoredByID($post->id))
        {
            $client->AddPost($reddit, $post);
            array_push($authors, $post->author);
            $post->getComments();
            $client->AddCommentsListing($post->comments);
            if (count($post->comments->comments) > 0)
            {
                $someComment = $client->GetCommentByID($post->comments->comments[0]->id);
            }
            
        }
    }
}

$authors = array_unique($authors);

$existing_authors = $client->UsersStored_ByName($authors);
$authors = array_diff($authors, $existing_authors);

//WriteDump("Novel Authors: ", $authors);

foreach ($authors as $author)
{
    $reddit->GetUserInfo($author, $author_id, $utc_timestamp, $link_score, $comment_score);
    $client->AddUser($author_id, $author, $utc_timestamp, $link_score, $comment_score);
}
