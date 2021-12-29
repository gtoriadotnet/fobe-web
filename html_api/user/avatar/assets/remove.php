<?php


/*
Alphaland 2021 
*/

//https://api.alphaland.cc/user/avatar/deequipItem?assetId=74

//headers

use Alphaland\Users\User;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
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
		User::DeequipAsset($user->id, $assetid);
	} catch (Exception $e) {
		http_response_code(500);
		$error = $e->getMessage();
	}
	echo json_encode(array("error" => $error));
}