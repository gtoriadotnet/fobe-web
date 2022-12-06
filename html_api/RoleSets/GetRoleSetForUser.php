<?php


/*
Fobe 2021 
*/

//headers

use Fobe\Games\Game;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
header('Content-Type: application/json');

//get params
$placeid = $_GET['placeId'];
$userid = $_GET['userId'];

$userInfo = array(
	"data" => array(
		"Rank" => Game::GetPersonalBuildServerRank($placeid, $userid),
	)
);

die(json_encode($userInfo));