<?php

use Finobe\Web\WebContextManager;

$body = '';

	if(isset($_POST['acceptfriend'])) 
	{
		acceptFriendRequest($_POST['acceptfriend']);
		header('Location: friend-requests');
	}
	if(isset($_POST['declinefriend'])) 
	{
		invalidateFriendRequest($_POST['declinefriend']);
		header('Location: friend-requests');
	}
	
	//all user friend requests
	$totalfriendrequests = getFriendRequests();
	
	//where each friend is stored
	$friendrequest_html = "";
	
	// Find out how many items are in the table
	$total = $pdo->prepare('SELECT COUNT(*) FROM friend_requests WHERE (rid = :u) AND valid = 1');
	$total->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$total->execute();
	$total = $total->fetchColumn();

	// How many items to list per page
	$limit = 18;

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
	$stmt = $pdo->prepare('SELECT * FROM friend_requests WHERE (rid = :u) AND valid = 1 ORDER BY whenSent DESC LIMIT :limit OFFSET :offset');

	// Bind the query params
	$stmt->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
	$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
	$stmt->execute();
	
	// Do we have any results?
	if ($stmt->rowCount() > 0) 
	{
		// Define how we want to fetch the results
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$iterator = new IteratorIterator($stmt);

		foreach($iterator as $request) 
		{
			$currentrequest = getUsername($request['sid']);
			$render = getPlayerRender($request['sid']);
			$friendrequest_html .= <<<EOT
			
			<li>
				<a class="a-nostyle" href="../profile/view?id={$request['sid']}">
					<img class="friends-avatar-front img-fluid" src="{$render}">
						<p>{$currentrequest}</p>
				</a>
				<div class="row text-center">
					<div class="col-sm padding-6px">
						<button type="submit" name="acceptfriend" value="{$request['sid']}" button class="btn btn-sm btn-success w-100"><i class="fas fa-check"></i></button>
					</div>
					<div class="col-sm padding-6px">
						<button type="submit" name="declinefriend" value="{$request['sid']}" button class="btn btn-sm btn-danger w-100"><i class="fas fa-times"></i></button>
					</div>
				</div>
			</li>
						
EOT;
		}		
	}
	else
	{
		$friendrequest_html = "No friend requests";
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
	<button type="button" onclick="location.href = '?page=1'" class="btn btn-danger"><<</button>
EOT;

	//page back
	$pageback = $page - 1;
	$pageback_html = <<<EOT
	<button type="button" onclick="location.href = '?page={$pageback}'" class="btn btn-danger">‹</button>
EOT;
	if ($pages > 5) //check if the page has more than 5 pages
	{
		//show the current page button
		$currentpage_html = <<<EOT
		<button type="button" onclick="location.href = '?page={$page}'" style="background-color:  #c82333;" class="btn btn-danger">$page</button>
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
					<button type="button" onclick="location.href = '?page={$b}'" style="background-color:  #c82333;" class="btn btn-danger">{$b}</button>
EOT;
				}
				else
				{
					$pages_html .= <<<EOT
					<button type="button" onclick="location.href = '?page={$b}'" class="btn btn-danger">{$b}</button>
EOT;
				}
			}
		}
	}
	else
	{
		for($i=0; $i<$pages; $i++)
		{
			$b=$i + 1;
			if ($page == $b)
			{
				$pages_html .= <<<EOT
				<button type="button" onclick="location.href = '?page={$b}'" style="background-color:  #c82333;" class="btn btn-danger">{$b}</button>
EOT;
			}
			else
			{
				$pages_html .= <<<EOT
					<button type="button" onclick="location.href = '?page={$b}'" class="btn btn-danger">{$b}</button>
EOT;
			}
		}	
	}
	
	//page forward
	$pageforward = $page + 1;
	$pageforward_html = <<<EOT
	<button type="button" onclick="location.href = '?page={$pageforward}'" class="btn btn-danger">›</button>
EOT;
	
	$last_button_html = <<<EOT
	<button type="button" onclick="location.href = '?page={$pages}'" class="btn btn-danger">>></button>
EOT;
}

if ($pages != 0)
{
	if ($_GET['page'] == 0)
	{
		WebContextManager::Redirect("friend-requests?page=1");
	}
	elseif ($_GET['page'] == $pages + 1)
	{
		WebContextManager::Redirect("friend-requests?page=".$pages."");
	} 
}
elseif ($pages == 0)
{
	if (!$_GET['page'])
	{
		WebContextManager::Redirect("friend-requests?page=1");
	}
	elseif($_GET['page'] > 1)
	{
		WebContextManager::Redirect("friend-requests?page=1");
	}
}

$body = <<<EOT
<div class="container text-center">
	<h5>Friend Requests ({$totalfriendrequests->rowCount()})</h5>
	<div class="card">
		<div class="card-body friend-requests-container">
			<ul class="friend-requests-padding">
				<form action="" method="post">
					{$friendrequest_html}
				</form>
			</ul>
		</div>
	</div>
	<div class="container">
		<div class="btn-group" role="group" aria-label="First group">
			{$beginning_button_html}
			{$pageback_html}
			{$currentpage_html}
			{$pages_html}
			{$pageforward_html}
			{$last_button_html}
		</div>
	</div>
</div>
EOT;
		
pageHandler();
$ph->body = $body;
$ph->output();
?>