<?php


/*
Fobe 2021 
*/

//headers

use Fobe\Users\User;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//local user id
$localuser = $user->id;

//get params
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$limit || !$page || !$localuser)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//query
$query = "SELECT * FROM `user_shouts` WHERE EXISTS (SELECT * FROM friends WHERE (rid = :uid and sid = user_shouts.uid OR rid = user_shouts.uid and sid = :uid2) AND valid = 1) AND NOT EXISTS (SELECT * FROM user_bans WHERE uid = user_shouts.uid AND valid = 1) ORDER BY whenShouted DESC"; 

//count how many shouts without offset/limit
$shoutscount = $pdo->prepare($query);
$shoutscount->bindParam(':uid', $localuser, PDO::PARAM_INT);
$shoutscount->bindParam(':uid2', $localuser, PDO::PARAM_INT);
$shoutscount->execute();
$shoutscount = $shoutscount->rowCount();

//data for pages
$total = $shoutscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$shouts = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$shouts->bindParam(':uid', $localuser, PDO::PARAM_INT);
$shouts->bindParam(':uid2', $localuser, PDO::PARAM_INT);
$shouts->bindParam(':limit', $limit, PDO::PARAM_INT);
$shouts->bindParam(':offset', $offset, PDO::PARAM_INT);
$shouts->execute();

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
	"pageResults" => (int)$shouts->rowCount()
);

foreach($shouts as $shout)
{
	$username = getUsername($shout['uid']);
	$userid = (int)$shout['uid'];
	//$shoutrender = getPlayerRender($shout['uid']);
	$timestamp = $shout['whenShouted'];
	$shout = cleanOutput($shout['shout']);

	$whenshout = "";
	//if($timestamp + 86400 > time()) //if it hasnt been a day since posted time
	//{
	//	$whenshout = date('h:i A', $timestamp);
	//}
	//else //been a day so we show date
	//{
		$whenshout = date("m/d/Y", $timestamp);
	//}
	$sitestatus = User::SiteStatus($user->id);
	
	$shoutInfo = array(
		"userid" => $userid,
		"username" => $username,
		"shout" => $shout,
		"date" => $whenshout,
		"siteStatus" => $sitestatus,
		"thumbnail" => "https://api.idk16.xyz/users/thumbnail?userId=".$userid."&headshot=true"
	);
	
	array_push($jsonData, $shoutInfo);
}
// ...

die(json_encode($jsonData));