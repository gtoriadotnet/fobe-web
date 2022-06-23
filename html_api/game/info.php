<?php


/*
Finobe 2021 
*/

//headers

use Finobe\Games\Game;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$place = $_GET['id'];

//initial checks
if(!$place) {
	http_response_code(400);
}

$assetinfo = getAssetInfo($place);

$userInfo = array(
	"id" => $assetinfo->id,
	"Name" => cleanOutput($assetinfo->Name),
	"Description" => cleanOutput($assetinfo->Description),
	"Visits" => $assetinfo->Visited,
	"Created" => date("m/d/Y", $assetinfo->Created),
	"Creator" => getUsername($assetinfo->CreatorId),
	"CreatorId" => $assetinfo->CreatorId,
	"isPersonalServer" => boolval($assetinfo->isPersonalServer),
	"playPermission" => Game::UserAccess($assetinfo->id, $user->id),
	"canManage" => boolval($assetinfo->CreatorId == $user->id || $user->IsAdmin()),
	"CommentsEnabled" => boolval($assetinfo->IsCommentsEnabled),
	"PersonalServerWhitelist" => boolval($assetinfo->isGameWhitelisted),
	"MaxPlayers" => $assetinfo->MaxPlayers,
	"creatorThumbnail" => getPlayerRender($assetinfo->CreatorId),
	"placeThumbnail" => handleGameThumb($assetinfo->id)
);

die(json_encode($userInfo));