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

//initial checks
if (!$userid) {
	$userid = $user->id;
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

$games = getAllGames($userid);

$jsonData = array(
	"gamesCount" => $games->rowCount(),
);

foreach($games as $game) {
	$gameID = $game['id']; //id of the game
	$name = cleanOutput($game['Name']); //name of the game
	$desc = cleanOutput($game['Description']); //description of the game
	$visits = $game['Visited']; //visit count of the game
	$creation = date("m/d/Y", $game['Created']); //creation date of the game NOTE: to get the date, use UNIX_TIMESTAMP()
	$creatorN = getUsername($game['CreatorId']); //creator of the game username
	$placerender = handleGameThumb($gameID);
	
	$placeInfo = array(
		"id" => $gameID,
		"name" => $name,
		"description" => $desc,
		"visits" => $visits,
		"creation" => $creation,
		"thumbnail" => $placerender
	);
	
	array_push($jsonData, $placeInfo);
}
// ...

die(json_encode($jsonData));