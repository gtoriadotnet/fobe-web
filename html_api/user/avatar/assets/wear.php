<?php


/*
Finobe 2021 
*/

//https://api.idk16.xyz/user/avatar/equipItem?assetId=

//headers

use Finobe\Users\User;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$assetid = (int)$_GET['assetId'];

if (!$assetid)
{
	http_response_code(400);
}
else
{
	$error = null;
	try {
		User::EquipAsset($user->id, $assetid);
	} catch (Exception $e) {
		http_response_code(500);
		$error = $e->getMessage();
	}
	echo json_encode(array("error" => $error));
}