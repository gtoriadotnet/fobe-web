<?php

/*
Finobe 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$assetid = $_GET['assetId'];
$page = $_GET['page'];
$limit = $_GET['limit'];

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
$query = "SELECT * FROM asset_comments WHERE aid = :aid ORDER BY whenCommented DESC"; 

//count how many games without offset/limit
$commentscount = $pdo->prepare($query);
$commentscount->bindParam(':aid', $assetid, PDO::PARAM_INT);
$commentscount->execute();
$commentscount = $commentscount->rowCount();

//data for pages
$total = $commentscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$comments = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$comments->bindParam(':aid', $assetid, PDO::PARAM_INT);
$comments->bindParam(':limit', $limit, PDO::PARAM_INT);
$comments->bindParam(':offset', $offset, PDO::PARAM_INT);
$comments->execute();

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
	"pageResults" => (int)$comments->rowCount()
);

foreach($comments as $comment)
{
	$userid = $comment['uid'];
	$username = getUsername($userid);
	$timestamp = $comment['whenCommented'];

	
	$whenposted = "";
	//if($timestamp + 86400 > time()) //if it hasnt been a day since posted time
	//{
	//	$whenposted = date('h:i A', $timestamp);
	//}
	//else //been a day so we show date
	//{
		$whenposted = date("m/d/Y", $timestamp);
	//}

	$comment = cleanOutput($comment['comment']);
	$thumbnail = getPlayerRender($userid);
	
	$commentsInfo = array(
		"userid" => $userid,
		"username" => $username,
		"date" => $whenposted,
		"comment" => $comment,
		"thumbnail" => "https://api.idk16.xyz/users/thumbnail?userId=".$userid."&headshot=true"
	);
	
	array_push($jsonData, $commentsInfo);
}
// ...

die(json_encode($jsonData));