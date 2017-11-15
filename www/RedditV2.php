<?php
abstract class RedditItem
{
	public $id;
	
	public function __construct($id)
	{
		$this->id = $id;
	}
}

abstract class AuthoredItem extends RedditItem
{
	public $author;
	public $created_utc;
	public $score;
	public $permalink;
	public $edited;
	
	public function __construct($id)
	{
		parent::__construct($id);
		$this->score = 0;
	}
}

class Post extends AuthoredItem
{
	public $text;
	public $text_html;
	public $subreddit;
	public $subreddit_id;
	public $link;
	public $title;
	
	public function __construct($id)
	{
		parent::__construct($id);
	}
	
}

class Comment extends AuthoredItem
{
	public $text;
	public $text_html;
	public $parent_id;
	public $replies;
	public $post_id;
	
	public function __construct($id)
	{
		parent::__construct($id);
		$this->replies = array();
	}
}

class User extends RedditItem
{
	public $name;
	public $utc_timestamp;
	public $link_score;
	public $comment_score;
	
	public function __construct($id)
	{
		parent::__construct($id);
		$this->link_score = 0;
		$this->comment_score = 0;
	}
}

class Subreddit extends RedditItem
{
	public $name;
	
	public function __construct($id)
	{
		parent::__construct($id);
	}
}

class RedditAPI
{
	private $client;
	
	public $kinds = [
	't1' => 'comment',
	't2' => 'account',
	't3' => 'link',
	't4' => 'message',
	't5' => 'subreddit',
	't6' => 'award',
	];
	
	public $usersCache = [];
	public $postsCache = [];
	public $subsCache = [];
	public $commentsCache = [];
	
	
	private function getJSON($uri, $queryStr = '')
	{
		$cli = $this->client;
		$redditURL = 'https://www.reddit.com';
		try
		{
			if ($queryStr === '')
			{
				$str = $cli(
					$redditURL . $uri . '.json?'
					. 'raw_json=1'
				);
			}
			else
			{
				$str = $cli(
					$redditURL . $uri . '.json?'
					. $queryStr . '&raw_json=1'
				);
			}
			return json_decode($str, true);
		}
		catch (Exception $ex)
		{
			return NULL;
		}
	}
	
	public function __construct($client)
	{
		$this->client = $client;
	}
	
	public function getUserByName($name)
	{
		$json = $this->getJson('/user/' . $name
			. "/about/");
		
		return $this->parse($json);
	}
	
	public function getSubredditListingByName($name, $count=10)
	{
		$json = $this->getJson(
			'/r/' . $name,
			'limit=' . $count
		);
		
		return $this->parse($json);
	}
	
	public function getSubredditByName($name)
	{
		$json = $this->getJson('/r/' . $name . '/about/');
		
		return $this->parse($json);
	}
	
	public function getComments($postID, $limit = 0)
	{
		if ($limit > 0)
		{
			$json = $this->getJson(
				'/comments/' . $postID . '/',
				'limit=' . $limit
			);
		}
		else
		{
			$json = $this->getJson(
				'/comments/' . $postID . '/'
			);
		}
		
		$comments = array();
		
		foreach ($json as $commentJson)
		{
			array_push($comments, $this->parse($commentJson));
		}
		
		return $comments;
		
	}
	
	
	
	
	
	
	
	private function parse($json)
	{
		if ($json === null)
		{
			return null;
		}
		
		$kind = strtolower($json['kind']);
		if ($kind === 'listing')
		{
			if (in_array('data', array_keys($json)))
			{
				if (in_array('children', array_keys($json['data'])))
				{
					return $this->parseListing($json['data']['children']);
				}
			}
			throw new Exception('Unknown format for listing!'."\n".var_export($json));
		}
		else if ($kind == 'more')
		{
			return $this->parseMore($json['data']);
		}
		else if (!in_array($kind, array_keys($this->kinds)))
		{
			echo "\n'" . $kind . "'\n";
			echo 'Keymap: ' . "\n";
			echo var_export($json);
			print_keymap($json);
			throw new Exception();
		}
		else if ($this->kinds[$kind] == 'link')
		{
			return $this->parsePost($json['data']);
		}
		else if ($this->kinds[$kind] == 'comment')
		{
			return $this->parseComment($json['data']);
		}
		else if ($this->kinds[$kind] == 'account')
		{
			return $this->parseUser($json['data']);
		}
		else if ($this->kinds[$kind] == 'subreddit')
		{
			return $this->parseSubreddit($json['data']);
		}
		
		/*echo 'Kind: ' . $json['kind'] . "\n";
		echo $this->kinds[$json['kind']] . "\n";
		print_keymap($json); */
	}
	
	private function parseListing($json)
	{
		if ($json === null)
		{
			return [];
		}
		$arr = array();
		foreach ($json as $item)
		{
			if ($item === null)
			{
				continue;
			}
			array_push($arr, $this->parse($item));
		}
		return $arr;
	}
	
	private function parsePost($json)
	{
		if ($json == null)
		{
			return null;
		}
		
		$post = new Post($json['id']);
		$post->text = $json['selftext'];
		$post->text_html = $json['selftext_html'];
		$post->subreddit = $json['subreddit'];
		$post->subreddit_id = $json['subreddit_id'];
		$post->title = $json['title'];
		$post->score = $json['score'];
		$post->author = $json['author'];
		$post->created_utc = $json['created_utc'];
		$post->permalink = $json['permalink'];
		if ($json['is_self'] === false)
		{
			$post->link = $json['url'];
		}
		
		$this->postsCache[$post->id] = &$post;
		if (!array_key_exists($post->subreddit_id, $this->subsCache))
		{
			$sub = new Subreddit($post->subreddit_id);
			$sub->name = $post->subreddit;
			$this->subsCache[$sub->id] = &$sub;
		}
		
		if (!array_key_exists($post->author, $this->usersCache))
		{
			$this->usersCache[$post->author] = new User(null);
			$user = &$this->usersCache[$post->author];
			$user->name = $post->author;
		}
		
		return $post;
	}
	
	
	private function parseSubreddit($json)
	{
		if ($json === null)
		{
			return null;
		}
		
		
		if (!array_key_exists($json['id'], $this->subsCache))
		{
			$sub = new Subreddit($json['id']);
			$sub->name = $json['display_name'];
			$this->subsCache[$sub->id] = &$sub;
		}
		
		return $this->subsCache[$json['id']];
	}
	
	private function parseComment($json)
	{
		if ($json === null)
		{
			return null;
		}
		
		$exists = array_key_exists($json['id'], $this->commentsCache);
		
		if ($exists)
		{
			if ($this->commentsCache[$json['id']]->permalink === null)
			{
				$exists = false;
			}
		}
		
		if (!$exists)
		{
			$comm = new Comment($json['id']);
			$comm->author = $json['author'];
			$comm->text = $json['body'];
			$comm->text_html = $json['body_html'];
			$comm->permalink = $json['permalink'];
			$comm->score = $json['score'];
			$comm->created_utc = $json['created_utc'];
			$comm->post_id = substr($json['link_id'], 3);
			
			if (substr($json['parent_id'], 0, 2) === 't3')
			{
				$comm->parent_id = null;
			}
			else
			{
				$comm->parent_id = substr($json['parent_id'], 3);
			}
			
			$comm->edited = (bool)$json['edited'];
			
			if (array_key_exists('replies', $json))
			{
				//echo var_export($json['replies'])."\n";
				if (is_array($json['replies']))
				{
					if (array_key_exists('kind', $json['replies']))
					{
						$comm->replies = $this->parse($json['replies']);
					}
				}
				
			}
			
			echo var_export($comm)."\n";
			
			$this->commentsCache[$comm->id] = &$comm;
		}
		
		return $this->commentsCache[$json['id']];
	}
}

class RedditDB
{
	private $api;
	private $DB;
	
	public function __construct($webClient)
	{
		$this->api = new RedditAPI($webClient);
		//$this->DB = new RedditSQLClient(...);
	}
	
	public function getSubredditListingByName($name, $limit = 10)
	{
		return $this->api->getSubredditListingByName($name, $limit);
	}
	
	public function usersNotInDB($users)
	{
		//TODO...
		$stringsArr = is_string($users[array_keys($users)[0]]);
		if ($stringsArr)
		{
			$names = $users;
		}
		else
		{
			$names = array_column($users, 'name');
		}
		
		$oldNames = [];
		//$oldNames = $DB->usersInDBByName($names); <- TODO
		$newNames = array_diff($names, $oldNames);
		
		if ($stringsArr)
		{
			return $newNames;
		}
		
		
		$newUsers = [];
		foreach ($users as &$user)
		{
			if (in_array($user->name, $newNames))
			{
				array_push($newUsers, $user);
			}
		}
		return $newUsers;
	}
	
	public function addUsersToDB($arr)
	{
		//TODO...
	}
	
	
	
	
	
	
	
	
	
	public function subsNotInDB($subs)
	{
		//TODO...
		$stringsArr = is_string($subs[array_keys($subs)[0]]);
		if ($stringsArr)
		{
			$names = $subs;
		}
		else
		{
			$names = array_column($subs, 'name');
		}
		
		$oldNames = [];
		//$oldNames = $DB->subsInDBByName($names); <- TODO
		$newNames = array_diff($names, $oldNames);
		
		if ($stringsArr)
		{
			return $newNames;
		}
		
		
		$newSubs = [];
		foreach ($subs as &$sub)
		{
			if (in_array($sub->name, $newNames))
			{
				array_push($newSubs, $sub);
			}
		}
		return $newSubs;
	}
	
	public function addSubsToDB($arr)
	{
		//TODO...
	}
	
	
	
	
	
	
	
	
	
	
	
	
	public function commitCache()
	{
		$newUsers = $this->usersNotInDB($this->api->usersCache);
		
		foreach ($newUsers as &$user)
		{
			$user = $this->api->getUserByName($user->name);
		}
		
		$this->addUsersToDB($newUsers);
		
		
		$newSubs = $this->subsNotInDB($this->api->subsCache);
		
		foreach ($newSubs as &$sub)
		{
			$sub = $this->api->getSubredditByName($sub->name);
		}
		
		$this->addSubsToDB($newSubs);
		
		
	}
}





function print_keymap($arr, $depth = 0)
{
	if (!is_array($arr))
	{
		return;
	}
	
	foreach (array_keys($arr) as $key)
	{
		$str = '';
		for ($i = 0; $i < $depth; $i = $i + 1)
		{
			$str = $str . "\t";
		}
		echo $str . $key . "\n";
		print_keymap($arr[$key], $depth+1);
	}
}

ini_set('allow_url_fopen', 'On');
ini_set('allow_url_include', 'On');

;

$api = new RedditAPI(function ($url) {
	return file_get_contents($url);
});



$post1 = $api->getSubredditListingByName('gaming', 1)[0];

$reddit = new RedditDB(function ($url) {
	return file_get_contents($url);
});

$post1 = $reddit->getSubredditListingByName('gaming', 1)[0];

$api->getComments($post1->id, 3);

$reddit->commitCache();


?>
    