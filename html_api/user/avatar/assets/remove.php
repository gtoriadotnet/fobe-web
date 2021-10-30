<?php


/*
Alphaland 2021 
*/

//https://api.alphaland.cc/user/avatar/deequipItem?assetId=74

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
	$deequip = deequipItem($assetid);
	if ($deequip !== true)
	{
		header('Content-Type: application/json');
		http_response_code(500);
		echo json_encode(array("error" => $deequip));
	}
}