<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$data = json_decode(file_get_contents('php://input'));
$name = $data->name;

if (!$data)
{
	http_response_code(400);
}
else
{
	$createoutfit = createOutfit($name, $user->id);
	if ($createoutfit === true) {
		$createoutfit = "Outfit Created";
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $createoutfit));
}