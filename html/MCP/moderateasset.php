<?php

/*
Alphaland 2021 
*/


//headers

use Alphaland\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

if(!$user->isStaff())
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
		logStaffAction("Moderated Asset ".$assetid);
		$moderate = "Moderated Asset";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $moderate));
}