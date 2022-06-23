<?php

/*
Finobe 2021 
*/


//headers

use Finobe\Groups\Group;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'));
$name = $data->name;
$description = $data->description;
$joinapprovals = (bool)$data->approvals;
$img = $data->emblem;

if (!$data)
{
	http_response_code(400);
}
else
{
	$newgroup = null;
	try {
		if (Group::Create($name, $description, $joinapprovals, $user->id, $img)) {
			$newgroup = "Group Created";
		}
	} catch (Exception $e) {
		$newgroup = $e->getMessage();
	}
	
	echo json_encode(array("alert" => $newgroup));
}