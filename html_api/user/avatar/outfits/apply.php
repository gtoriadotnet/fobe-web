<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$data = json_decode(file_get_contents('php://input'));
$id = $data->id;

if (!$data)
{
	http_response_code(400);
}
else
{
	$applyoutfit = applyOutfit($user->id, $id);
	if ($applyoutfit === true) {
		$applyoutfit = "Outfit Applied";
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $applyoutfit));
}