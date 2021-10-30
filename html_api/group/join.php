<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$groupid = $_GET['id'];

if (!$groupid)
{
	http_response_code(400);
}
else
{
	$joingroup = attemptJoinGroup($groupid);
	if ($joingroup === true) {
		$joingroup = "Joined Group";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $joingroup));
}