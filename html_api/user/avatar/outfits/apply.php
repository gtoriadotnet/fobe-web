<?php


/*
Alphaland 2021 
*/

//headers

use Alphaland\Users\Outfit;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$data = json_decode(file_get_contents('php://input'));
$id = $data->id;

if (!$data)
{
	http_response_code(400);
}
else
{	try {
		if (Outfit::ApplyOutfit($user->id, $id)) {
			$applyoutfit = "Outfit Applied";
		}
	} catch (Exception $e) {
		$applyoutfit = $e->getMessage();
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $applyoutfit));
}