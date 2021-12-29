<?php

/*
Alphaland 2021 
*/


//headers

use Alphaland\Groups\Group;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$groupid = $_GET['id'];

if (!$groupid)
{
	http_response_code(400);
}
else
{
	$leavegroup = null;
	try {
		if (Group::Leave($user->id, $groupid)) {
			$leavegroup = "Left Group";
		}
	} catch (Exception $e) {
		$leavegroup = $e->getMessage();
	}
	
	echo json_encode(array("alert" => $leavegroup));
}