<?php

/*
Fobe 2021 
*/

use Fobe\Users\User;

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userID = (int)$_GET['userId'];
$assetID = (int)$_GET['assetId'];

$gInfo = getAssetInfo($assetID);
$uInfo = User::GetUserInfo($userID);

function json($can) 
{
	return json_encode(array("Success" => true, "CanManage" => $can));
}

if($gInfo !== false) 
{
	if($gInfo->CreatorId == $userID || $uInfo->rank == 3)
	{
		die(json(true));
	}
}
die(json(false));