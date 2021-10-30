<?php


/*
Alphaland 2021
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$limit || !$page || !$userid)
{
	http_response_code(400);
	die();
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
	die();
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

//query GHETTO CUZ BINDPARAM SUCKS
$query = 'SELECT * FROM friends WHERE (rid = :rid OR sid = :sid) AND valid = 1 ORDER BY whenAccepted DESC'; 

//count how many shouts without offset/limit
$friendscount = $pdo->prepare($query);
$friendscount->bindParam(":rid", $userid, PDO::PARAM_INT);
$friendscount->bindParam(":sid", $userid, PDO::PARAM_INT);
$friendscount->execute();
$friendscount = $friendscount->rowCount();

//data for pages
$total = $friendscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$friends = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$friends->bindParam(":rid", $userid, PDO::PARAM_INT);
$friends->bindParam(":sid", $userid, PDO::PARAM_INT);
$friends->bindParam(':limit', $limit, PDO::PARAM_INT);
$friends->bindParam(':offset', $offset, PDO::PARAM_INT);
$friends->execute();

//final check to see if page is invalid 
if ($pages > 0)
{
	if ($page > $pages)
	{
		http_response_code(400);
	}
}

$jsonData = array(
	"friendsCount" => $friendscount,
	"pageCount" => $pages,
	"pageResults" => (int)$friends->rowCount()
);

foreach ($friends as $friend)
{
	$frienduserid = -1;
	if ($friend['sid'] == $userid) {
		$frienduserid = $friend['rid'];
	} elseif ($friend['rid'] == $userid) {
		$frienduserid = $friend['sid'];
	} else {
		continue;
	}
	
	$friendInfo = array (
		"userid" => $frienduserid,
		"username" => getUsername($frienduserid),
		"thumbnail" => getPlayerRender($frienduserid),
	);
	
	array_push($jsonData, $friendInfo);	
}
// ...

die(json_encode($jsonData));