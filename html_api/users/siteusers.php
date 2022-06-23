<?php

/*
Finobe 2021 
*/


//headers

use Finobe\Users\User;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$page = $_GET['page'];
$limit = $_GET['limit'];
$keyword = substr((string)$_GET['keyword'], 0, 32); //32 limit
$keywordq = '%'.$keyword.'%'; //query

//initial checks
if (!$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//query
$query = "SELECT * FROM users WHERE username LIKE :u AND NOT EXISTS (SELECT * FROM user_bans WHERE uid = users.id AND valid = 1) ORDER BY (SELECT COUNT(*) FROM game_presence WHERE uid = users.id AND(lastPing + 50) > UNIX_TIMESTAMP()) DESC, lastseen DESC"; 

//count how many users without offset/limit
$usercount = $pdo->prepare($query);
$usercount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$usercount->execute();
$usercount = $usercount->rowCount();

//data for pages
$total = $usercount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$users = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$users->bindParam(':u', $keywordq, PDO::PARAM_STR);
$users->bindParam(':limit', $limit, PDO::PARAM_INT);
$users->bindParam(':offset', $offset, PDO::PARAM_INT);
$users->execute();

//final check to see if page is invalid 
if ($pages > 0)
{
	if ($page > $pages)
	{
		http_response_code(400);
	}
}

//construct the json array
$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$users->rowCount(),
	"keyword" => $keyword
);

foreach($users as $user)
{
	$id = $user['id'];
	$username = $user['username'];
	$blurb = cleanOutput($user['blurb'], false); //pass false to not add html linebreaks
	$sitestatus = User::SiteStatus($id);
	$lastseen = date("m/d/Y", $user['lastseen']);
	$thumbnail = getPlayerRender($user['id']);
	
	$jsonInfo = array(
		"id" => $id,
		"username" => $username,
		"blurb" => $blurb,
		"siteStatus" => $sitestatus,
		"lastseen" => $lastseen,
		"thumbnail" => "https://api.idk16.xyz/users/thumbnail?userId=".$id."&headshot=true"
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));