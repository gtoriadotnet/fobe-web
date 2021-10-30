<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$groupid = (int)$_GET['groupid'];
$post = cleanInput(json_decode(file_get_contents('php://input'))->post);

if (!$groupid)
{
	http_response_code(400);
}
else
{
	$placepost = submitPost($groupid, $post);
	if ($placepost === true) {
		$placepost = "Post Placed";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $placepost));
}