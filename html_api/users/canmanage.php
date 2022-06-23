<?php

/*
Finobe 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userID = (int)$_GET['userId'];
$assetID = (int)$_GET['assetId'];

$gInfo = getAssetInfo($assetID);

function json($can) 
{
	return json_encode(array("Success" => true, "CanManage" => $can));
}

if($gInfo !== false) 
{
	if($gInfo->CreatorId == $userID || $userID == 2)
	{
		die(json(true));
	}
}
die(json(false));