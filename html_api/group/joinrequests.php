<?php


/*
	Finobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$groupid = (int)$_GET['id'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!groupid || !$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//cool query? single line conditions are pog
$query = "SELECT * FROM group_join_requests WHERE groupid = :gid ORDER BY whenRequested DESC"; 

//count how many shouts without offset/limit
$requestscount = $pdo->prepare($query);
$requestscount->bindParam(":gid", $groupid, PDO::PARAM_INT);
$requestscount->execute();
$requestscount = $requestscount->rowCount();

//data for pages
$total = $requestscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$requests = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$requests->bindParam(":gid", $groupid, PDO::PARAM_INT);
$requests->bindParam(':limit', $limit, PDO::PARAM_INT);
$requests->bindParam(':offset', $offset, PDO::PARAM_INT);
$requests->execute();

$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$requests->rowCount()
);

foreach($requests as $request)
{
	$userid = $request['userid']; //id of the game
	$username = getUsername($userid);
	$thumbnail = getPlayerRender($userid);
	
	$requestInfo = array(
		"userid" => $userid,
		"username" => $username,
		"thumbnail" => $thumbnail,
	);
	array_push($jsonData, $requestInfo);
}
// ...

die(json_encode($jsonData));