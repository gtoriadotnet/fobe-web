<?php

/*
Finobe 2021 
This is the API used to return all site games in JSON format with a provided limit, page and optional keyword
This is parsed with javascript on the users end, this allows the user to handle more of the processing power
*/


//headers

use Finobe\Games\Game;

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
$query = "SELECT * FROM assets WHERE AssetTypeId = 9 AND name LIKE :u ORDER BY (SELECT COUNT(*) FROM game_presence WHERE placeid = assets.id AND (lastPing + 50) > UNIX_TIMESTAMP()) DESC, Visited DESC"; 

//count how many games without offset/limit
$gamescount = $pdo->prepare($query);
$gamescount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$gamescount->execute();
$gamescount = $gamescount->rowCount();

//data for pages
$total = $gamescount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$games = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$games->bindParam(':u', $keywordq, PDO::PARAM_STR);
$games->bindParam(':limit', $limit, PDO::PARAM_INT);
$games->bindParam(':offset', $offset, PDO::PARAM_INT);
$games->execute();

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
	"pageResults" => (int)$games->rowCount(),
	"keyword" => $keyword
);

foreach($games as $game)
{
	$gameID = $game['id']; //id of the game
	$name = cleanOutput($game['Name']); //name of the game
	$desc = cleanOutput($game['Description']); //description of the game
	$visits = $game['Visited']; //visit count of the game
	$creation = date("m/d/Y", $game['Created']); //creation date of the game NOTE: to get the date, use UNIX_TIMESTAMP()
	$creatorN = getUsername($game['CreatorId']); //creator of the game username
	$playercount = Game::TotalPlayerCount($gameID); //players in the game
	$placerender = handleGameThumb($gameID);
	
	$placeInfo = array(
		"id" => $gameID,
		"name" => $name,
		"description" => $desc,
		"visits" => $visits,
		"creation" => $creation,
		"creatorName" => $creatorN,
		"playerCount" => $playercount,
		"thumbnail" => $placerender
	);
	
	array_push($jsonData, $placeInfo);
}
// ...

die(json_encode($jsonData));