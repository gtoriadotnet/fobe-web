<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$assetid = (int)$_GET['assetId'];
$comment = cleanInput(json_decode(file_get_contents('php://input'))->comment);

if (!$assetid)
{
	http_response_code(400);
}
else
{
	$placecomment = placeAssetComment($assetid, $comment);
	if ($placecomment === true) {
		$placecomment = "Comment Placed";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $placecomment));
}