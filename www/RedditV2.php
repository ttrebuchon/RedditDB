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
	public $suspended;
	
	public function __construct($id)
	{
		parent::__construct($id);
		$this->link_score = 0;
		$this->comment_score = 0;
		$this->suspended = false;
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

class WebClient
{
	private $client;

	private $cache = [];

	public function __construct($client)
	{
		$this->client = $client;
	}

	public function get($url)
	{
		$cli = &$this->client;
		if (!array_key_exists($url, $this->cache))
		{
			$this->cache[$url] = $cli($url);
		}
		
		WriteLine("Making call: '" . $url . "'");
		
		return $this->cache[$url];
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
				$str = $this->client->get(
					$redditURL . $uri . '.json?'
					. 'raw_json=1'
				);
			}
			else
			{
				$str = $this->client->get(
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
		$this->client = new WebClient($client);
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

	public function getPostByID($id)
	{
		$json = $this->getJson('/' . $id . '/', 'limit=1');

		return $this->parsePost($json[0]['data']['children'][0]['data']);
	}

	public function getCommentByID($post_id, $id)
	{
		if ($post_id === null || $id === null)
		{
			return null;
		}
		$json = $this->getJson('/comments/' . $post_id . '/' . $id, 'limit=1');

		return $this->parseComment($json[1]['data']['children'][0]['data'], false);
		// WriteDump("Comment: ", $json);
		// throw new Exception();

		// return $this->parsePost($json[0]['data']['children'][0]['data']);
	}








	public function commentCached($id, $retrieved = true)
	{
		$exists = array_key_exists($id, $this->commentsCache);
		
		if ($exists)
		{
			if (!$retrieved)
			{
				return true;
			}
			return ($this->commentsCache[$id]->permalink !== null);
		}

		return false;
	}

	public function userCached($name, $retrieved = true)
	{
		$exists = array_key_exists($name, $this->usersCache);
		
		if ($exists)
		{
			if (!$retrieved)
			{
				return true;
			}
			return ($this->usersCache[$name]->id !== null);
		}

		return false;
	}

	public function subCached($id, $retrieved = true)
	{
		$exists = array_key_exists($id, $this->subsCache);
		
		if ($exists)
		{
			if (!$retrieved)
			{
				return true;
			}
			return ($this->subsCache[$id]->name !== null);
		}

		return false;
	}

	public function postCached($id, $retrieved = true)
	{
		$exists = array_key_exists($id, $this->postsCache);
		
		if ($exists)
		{
			if (!$retrieved)
			{
				return true;
			}
			return ($this->postsCache[$id]->permalink !== null);
		}

		return false;
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
			WriteLine("\n'" . $kind . "'\n");
			WriteLine('Keymap: ' . "\n");
			WriteLine(var_export($json, true));
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
		$post->subreddit_id = substr($json['subreddit_id'], 3);
		$post->title = $json['title'];
		$post->score = $json['score'];
		$post->author = $json['author'];
		$post->created_utc = $json['created_utc'];
		$post->permalink = $json['permalink'];
		if ($json['is_self'] === false)
		{
			$post->link = $json['url'];
		}
		
		$this->postsCache[$post->id] = $post;
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
	
	private function parseComment($json, $children = true)
	{
		if ($json === null)
		{
			return null;
		}

		$exists = $this->commentCached($json['id'], true);
		
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
			
			if (array_key_exists('replies', $json) && $children)
			{
				if (is_array($json['replies']))
				{
					if (array_key_exists('kind', $json['replies']))
					{
						$comm->replies = $this->parse($json['replies']);
					}
				}
				
			}

			if (!array_key_exists($comm->author, $this->usersCache))
			{
				$this->usersCache[$comm->author] = new User(null);
				$user = $this->usersCache[$comm->author];
				$user->name = $comm->author;
			}
			
			$this->commentsCache[$comm->id] = $comm;
		}
		if ($this->commentsCache[$json['id']]->id != $json['id'])
		{
			throw new Exception("Mismatched IDs??");
		}
		return $this->commentsCache[$json['id']];
	}

	private function parseUser($json)
	{
		if ($json === null)
		{
			return null;
		}

		if (!$this->userCached($json['name'], true))
		{
			if ($this->userCached($json['name'], false))
			{
				$usr = &$this->usersCache[$json['name']];
			}
			else
			{
				$usr = new User($json['id']);
			}
			$usr->id = $json['id'];
			$usr->name = $json['name'];
			$usr->link_score = $json['link_karma'];
			$usr->comment_score = $json['comment_karma'];
			$usr->suspended = false;
			$usr->utc_timestamp = $json['created_utc'];

			$this->usersCache[$json['name']] = $usr;
		}

		return $this->usersCache[$json['name']];
	}

	private function parseMore($json)
	{
		if ($json === null)
		{
			return null;
		}

		$type = strtolower(substr($json['name'], 0, 2));
		if ($this->kinds[$type] === 'comment')
		{
			$parent_id = null;
			if (strtolower(substr($json['parent_id'], 0, 2)) === 't1')
			{
				$parent_id = substr($json['parent_id'], 3);
			}
			$comments = [];
			foreach ($json['children'] as $id)
			{
				$comm = new Comment($id);
				$comm->parent_id = $parent_id;
				if ($parent_id != null)
				{
					if ($this->commentCached($parent_id, true))
					{
						$comm->post_id = $this->commentsCache[$parent_id]->post_id;
					}
				}
				else
				{
					$comm->post_id = substr($json['parent_id'], 3);
				}
				
				array_push($comments, $comm);
				$this->commentsCache[$id] = $comm;
			}
			return $comments;
		}
		

		WriteLine(var_export($json, true));
		throw new Exception("Not Implemented");
	}
}

class RedditDB
{
	public $api;
	private $DB;
	
	public function __construct($webClient)
	{
		$this->api = new RedditAPI($webClient);
		$this->DB = new RedditSQLClient(DBHOST);
		$this->DB->rollback();
		$this->DB->endTransaction();
		$this->DB->enableFK();
	}
	
	public function API_GetSubredditListingByName($name, $limit = 10)
	{
		return $this->api->getSubredditListingByName($name, $limit);
	}

	public function API_GetComments($postID, $limit = 0)
	{
		return $this->api->getComments($postID, $limit);
	}







	
	public function usersNotInDB($users)
	{
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
		$oldNames = $this->DB->usersStored_ByName($names);
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
		foreach ($arr as $user)
		{
			$this->DB->addUser($user);
		}
	}
	
	
	
	
	
	
	
	
	
	public function subsNotInDB($subs)
	{
		$stringsArr = is_string($subs[array_keys($subs)[0]]);
		if ($stringsArr)
		{
			$names = $subs;
		}
		else
		{
			$names = array_column($subs, 'name');
		}
		

		$oldNames = $this->DB->subredditsStored_ByName($names);
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
		foreach ($arr as $sub)
		{
			$this->DB->addSubreddit($sub->id, $sub->name);
		}
	}

	public function postsNotInDB($posts)
	{
		$stringsArr = is_string($posts[array_keys($posts)[0]]);
		if ($stringsArr)
		{
			$ids = $posts;
		}
		else
		{
			$ids = array_column($posts, 'id');
		}
		

		$oldIDs = $this->DB->postsStored_ByID($ids);
		$newIDs = array_diff($ids, $oldIDs);
		
		if ($stringsArr)
		{
			return $newIDs;
		}
		
		
		$newPosts = [];
		foreach ($posts as &$post)
		{
			if (in_array($post->id, $newIDs))
			{
				array_push($newPosts, $post);
			}
		}
		return $newPosts;
	}
	
	public function addPostsToDB($arr)
	{
		foreach ($arr as $post)
		{
			$this->DB->addPost(null, $post);
		}
	}

	public function commentsNotInDB($comments)
	{
		$stringsArr = is_string($comments[array_keys($comments)[0]]);
		if ($stringsArr)
		{
			$ids = $comments;
		}
		else
		{
			$ids = array_column($comments, 'id');
		}
		

		$oldIDs = $this->DB->commentsStored_ByID($ids);
		$newIDs = array_diff($ids, $oldIDs);
		
		if ($stringsArr)
		{
			return $newIDs;
		}
		
		
		$newComments = [];
		foreach ($comments as $comment)
		{
			if (in_array($comment->id, $newIDs))
			{
				array_push($newComments, $comment);
			}
		}
		return $newComments;
	}
	
	public function addCommentsToDB($arr)
	{
		foreach ($arr as $comment)
		{
			$this->DB->addComment($comment);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	public function commitCache($fillBlankComments = true)
	{
		$this->DB->startTransaction();



		$newUsers = $this->usersNotInDB($this->api->usersCache);
		
		foreach ($newUsers as $user)
		{
			if (!$this->api->userCached($user->name, true))
			{
				$tmp = $this->api->getUserByName($user->name);
				if ($tmp !== null)
				{
					$user = $tmp;
				}
			}
		}
		
		$this->addUsersToDB($newUsers);
		
		


		$newSubs = $this->subsNotInDB($this->api->subsCache);
		
		foreach ($newSubs as &$sub)
		{
			if (!$this->api->subCached($sub->id, true))
			{
				$sub = $this->api->getSubredditByName($sub->name);
			}
			
		}
		
		$this->addSubsToDB($newSubs);

		$this->DB->endTransaction();
		
		$this->DB->startTransaction();

		$newPosts = $this->postsNotInDB($this->api->postsCache);
		
		foreach ($newPosts as &$post)
		{
			if (!$this->api->postCached($post->id, true))
			{
				$post = $this->api->getPostByID($post->id);
			}
		}
		
		$this->addPostsToDB($newPosts);

		$this->DB->endTransaction();

		$newComments = $this->commentsNotInDB($this->api->commentsCache);

		//WriteDump("newComments: ", array_column($newComments, 'id'));
		
		// $commentsDump = array();
		// foreach ($this->api->commentsCache as $key => &$comment)
		// {
		// 	$commentsDump[$key] = $comment->id;
		// }
		// WriteDump("commentsCache: ", $commentsDump);
		// throw new Exception();

		//$this->DB->startTransaction();
		$this->DB->disableFK();
		
		foreach ($newComments as &$comment)
		{
			if (!$this->api->commentCached($comment->id, true) && $fillBlankComments)
			{
				if ($comment->post_id === null)
				{
					if (array_key_exists($comment->parent_id, $this->api->commentsCache))
					{
						$comment->post_id = $this->api->commentsCache[$comment->parent_id]->post_id;
					}
				}
				$comment = $this->api->getCommentByID($comment->post_id, $comment->id, false);
			}

			if ($comment !== null)
			{
				if ($comment->id !== null && $comment->post_id !== null)
				{
					$this->addCommentFromCache($comment->id);
					WriteLine("Comment '" . $comment->id . "' added...");
				}
			}
		}

		$this->DB->enableFK();
		//$this->DB->endTransaction();
		
		//$this->DB->disableFK();
		//$this->addCommentsToDB($newComments);
		//$this->DB->enableFK();
	}

	private function addCommentFromCache($id)
	{
		//TODO(?)
		$comment = $this->api->commentsCache[$id];
		$this->DB->addComment($comment);
	}

	public function dropDatabase()
	{
		$this->DB->dropSchema();
	}

	public function createDatabase()
	{
		$this->DB->initializeSchema();
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
		WriteLine($str . $key . "\n");
		print_keymap($arr[$key], $depth+1);
	}
}


?>
    