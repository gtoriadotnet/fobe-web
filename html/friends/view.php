<?php

use Fobe\Web\WebContextManager;

$body = '';

if(!isset($_GET['id'])) 
{
	WebContextManager::Redirect('view?id='. $GLOBALS['user']->id . '');
}

$id = (int)$_GET['id'];
$info = userInfo($id); // add true as a second param if u wanna use usernames instead
$username = getUsername($info->id);
$allfriends = getFriends($info->id);
	
// Find out how many items are in the table
$total = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE (rid = :u OR sid = :u2) AND valid = 1');
$total->bindParam(":u", $id, PDO::PARAM_INT);
$total->bindParam(":u2", $id, PDO::PARAM_INT);
$total->execute();
$total = $total->fetchColumn();

// How many items to list per page
$limit = 24;

// How many pages will there be
$pages = ceil($total / $limit);

// What page are we currently on?
$page = min($pages, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
	'options' => array(
	'default'   => 1,
	'min_range' => 1,
	 ),
)));

// Calculate the offset for the query
$offset = ($page - 1)  * $limit;

// Some information to display to the user
$start = $offset + 1;
$end = min(($offset + $limit), $total);

// Prepare the paged query
$stmt = $pdo->prepare('SELECT * FROM friends WHERE (rid = :u OR sid = :u2) AND valid = 1 ORDER BY whenAccepted DESC LIMIT :limit OFFSET :offset');

// Bind the query params
$stmt->bindParam(":u", $id, PDO::PARAM_INT);
$stmt->bindParam(":u2", $id, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
	
$friends_html = "";
$currentfriend = "";
$currentid = "";
	
// Do we have any results?
if($info !== false) 
{
	if ($stmt->rowCount() > 0) 
	{
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$iterator = new IteratorIterator($stmt);
			
		foreach($iterator as $friend) 
		{
			if ($friend['sid'] == $info->id)
			{
				$currentfriend = getUsername($friend['rid']); 
				$currentid = $friend['rid'];
			}
			elseif ($friend['rid'] == $info->id)
			{
				$currentfriend = getUsername($friend['sid']);
				$currentid = $friend['sid'];
			}
			
			$render = getPlayerRender($currentid);
				
			$friends_html .= <<<EOT
			<li>
				<a class="a-nostyle" href=../profile/view?id={$currentid}>
					<img class="friends-avatar-front" src="{$render}">
					<p>{$currentfriend}</p>
				</a>
			</li>
EOT;
		}
	}
}
else
{
	WebContextManager::Redirect("/404");
}

//page buttons handling {

$beginning_button_html = "";
$pageback_html = "";
$currentpage_html = "";
$pageforward_html = "";
$last_button_html = "";
$pages_html = "";

if ($pages > 1)
{
	$beginning_button_html = <<<EOT
		<button type="button" onclick="location.href = '?id={$id}&page=1'" class="btn btn-danger"><<</button>
EOT;

	//page back
	$pageback = $page - 1;
	$pageback_html = <<<EOT
		<button type="button" onclick="location.href = '?id={$id}&page={$pageback}'" class="btn btn-danger">‹</button>
EOT;

	if ($pages > 5) //check if the page has more than 5 pages
	{
		//show the current page button
		$currentpage_html = <<<EOT
		<button type="button" onclick="location.href = '?id={$id}&page={$page}'" style="background-color:  #c82333;" class="btn btn-danger">$page</button>
EOT;
		$currentpages = 0;
		for($i=$page; $i<$pages; $i++)
		{
			$b=$i + 1;
			$currentpages = $currentpages + 1;
			if ($currentpages <= 4) //we want 5 buttons per page, but since gay we have the extra button above this loop
			{
				if ($page == $b)
				{
					$pages_html .= <<<EOT
					<button type="button" onclick="location.href = '?id={$id}&page={$b}'" style="background-color:  #c82333;" class="btn btn-danger">{$b}</button>
EOT;
				}
				else
				{
					$pages_html .= <<<EOT
					<button type="button" onclick="location.href = '?id={$id}&page={$b}'" class="btn btn-danger">{$b}</button>
EOT;
				}
			}
		}
	} 
	else //doesnt have more than 5 pages, show all the pages available
	{
		for($i=0; $i<$pages; $i++)
		{
			$b=$i + 1;
			if ($page == $b)
			{
				$pages_html .= <<<EOT
				<button type="button" onclick="location.href = '?id={$id}&page={$b}'" style="background-color:  #c82333;" class="btn btn-danger">{$b}</button>
EOT;
			}
			else
			{
				$pages_html .= <<<EOT
				<button type="button" onclick="location.href = '?id={$id}&page={$b}'" class="btn btn-danger">{$b}</button>
EOT;
			}
		}	
	}
	
	//page forward
	$pageforward = $page + 1;
	$pageforward_html = <<<EOT
	<button type="button" onclick="location.href = '?id={$id}&page={$pageforward}'" class="btn btn-danger">›</button>
EOT;
	
	$last_button_html = <<<EOT
	<button type="button" onclick="location.href = '?id={$id}&page={$pages}'" class="btn btn-danger">>></button>
EOT;
}

//end page buttons handling }


$body = <<<EOT
<div class="container text-center">
	<h5>{$username}'s Friends ({$allfriends->rowCount()})</h5>
	<div class="card">
		<div class="card-body friends-container">
			<ul class="">
			{$friends_html}
			</ul>
		</div>
	</div>
	<div class="btn-group mt-2" role="group" aria-label="First group">
			{$beginning_button_html}
			{$pageback_html}
			{$currentpage_html}
			{$pages_html}
			{$pageforward_html}
			{$last_button_html}
		</div>
</div>
EOT;
pageHandler();
$ph->body = $body;
$ph->output();
?>