<?php

/*
Fobe 2021 
*/


//headers

use Fobe\Moderation\UserModerationManager;
use Fobe\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");

if(!$user->IsStaff())
{
    WebContextManager::Redirect("/");
}

$assetid = $_GET['id'];

if (!$assetid)
{
	http_response_code(400);
}
else
{
	$moderate = moderateAsset($assetid);
	if ($moderate === true) {
		UserModerationManager::LogAction("Moderated Asset ".$assetid);
		$moderate = "Moderated Asset";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $moderate));
}