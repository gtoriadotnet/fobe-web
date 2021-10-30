<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

if(!$user->isStaff())
{
    redirect("../home"); //u not admin nigga
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
			logStaffAction("Approved Asset ".$assetid);
			$approve = "Approved Asset";
		}
	} else {
		$approve = "Thumbnailer Offline";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $approve));
}