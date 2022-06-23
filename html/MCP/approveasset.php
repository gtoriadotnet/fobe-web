<?php

/*
Finobe 2021 
*/


//headers

use Finobe\Moderation\UserModerationManager;
use Finobe\Web\WebContextManager;

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
	if (isThumbnailerAlive()) {
		$approve = approveAsset($assetid);
		if ($approve === true) {
			UserModerationManager::LogAction("Approved Asset ".$assetid);
			$approve = "Approved Asset";
		}
	} else {
		$approve = "Thumbnailer Offline";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $approve));
}