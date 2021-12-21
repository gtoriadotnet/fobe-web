<?php


/*
Alphaland 2021 
*/

//headers

use Alphaland\Games\Game;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");

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
$query = "SELECT * FROM game_recents WHERE uid = :u ORDER by whenPlayed DESC"; 

//count how many recent games without offset/limit
$recentcount = $pdo->prepare($query);
$recentcount->bindParam(':u', $localuser, PDO::PARAM_INT);
$recentcount->execute();
$recentcount = $recentcount->rowCount();

//data for pages
$total = $recentcount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$recents = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$recents->bindParam(':u', $localuser, PDO::PARAM_INT);
$recents->bindParam(':limit', $limit, PDO::PARAM_INT);
$recents->bindParam(':offset', $offset, PDO::PARAM_INT);
$recents->execute();

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
	"pageResults" => (int)$recents->rowCount()
);

foreach($recents as $game)
{
	$placeinfo = getAssetInfo($game['gid']);
	$id = $game['gid'];
	$visits = $placeinfo->Visited;
	$placename = cleanOutput($placeinfo->Name);
	$playercount = Game::TotalPlayerCount($game['gid']);
	$creatorid = $placeinfo->CreatorId;
	$creator = getUsername($creatorid);
	$thumbnail = handleGameThumb($game['gid']);
	
	$jsonInfo = array(
		"id" => $id,
		"name" => $placename,
		"creator" => $creator,
		"creatorId" => $creatorid,
		"playerCount" => $playercount,
		"visits" => $visits,
		"thumbnail" => $thumbnail
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));