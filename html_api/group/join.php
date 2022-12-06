<?php

/*
Fobe 2021 
*/


//headers

use Fobe\Groups\Group;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$groupid = $_GET['id'];

if (!$groupid)
{
	http_response_code(400);
}
else
{
	$joingroup = null;
	try {
		if (Group::Join($groupid, $user->id)) {
			$joingroup = "Joined Group";
		}
	} catch (Exception $e) {
		$joingroup = $e->getMessage();
	}
	
	echo json_encode(array("alert" => $joingroup));
}