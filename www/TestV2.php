<?php
require_once("SQL.php");
require_once("RedditV2.php");

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

$callCount = 0;

$reddit = new RedditDB(function ($url) use(&$callCount) {
    $callCount = $callCount + 1;
    WriteLine("Making call " . $callCount . ": '" . $url . "'");
	return file_get_contents($url);
});

$reddit->dropDatabase();
$reddit->createDatabase();

$post1 = $reddit->API_GetSubredditListingByName('gaming', 1)[0];

$reddit->API_GetComments($post1->id, 3);

$posts = $reddit->API_GetSubredditListingByName('gaming', 1);
foreach ($posts as $post)
{
    $reddit->API_GetComments($post->id, 5);
}

// WriteDump("Count: ", count($reddit->api->commentsCache));
// WriteDump("API Comments Cache: ", $reddit->api->commentsCache);
// return;

$reddit->commitCache(true);



?>