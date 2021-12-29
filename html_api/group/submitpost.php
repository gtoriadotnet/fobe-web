<?php

/*
Alphaland 2021 
*/


//headers

use Alphaland\Groups\Group;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$groupid = (int)$_GET['groupid'];
$post = json_decode(file_get_contents('php://input'))->post;

if (!$groupid)
{
	http_response_code(400);
}
else
{
	$placepost = null;
	try {
		if (Group::CreatePost($groupid, $user->id, $post)) {
			$placepost = "Post Placed";
		}
	} catch (Exception $e) {
		$placepost = $e->getMessage();
	}
	echo json_encode(array("alert" => $placepost));
}