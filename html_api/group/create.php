<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

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
	$newgroup = createGroup($name, $description, $joinapprovals, $img);
	if ($newgroup === true) {
		$newgroup = "Group Created";
	}
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $newgroup));
}