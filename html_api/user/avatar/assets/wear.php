<?php


/*
Alphaland 2021 
*/

//https://api.alphaland.cc/user/avatar/equipItem?assetId=

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$assetid = (int)$_GET['assetId'];

if (!$assetid)
{
	http_response_code(400);
}
else
{
	$equip = equipItem($assetid);
	if ($equip !== true)
	{
		header('Content-Type: application/json');
		http_response_code(500);
		echo json_encode(array("error" => $equip));
	}
}