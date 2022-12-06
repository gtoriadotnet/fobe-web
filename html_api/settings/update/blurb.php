<?php

/*
Fobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");

$userid = $user->id;
$data = json_decode(file_get_contents('php://input'));

if (!$data)
{
	http_response_code(400);
}
else
{	
	$blurb = $data->blurb;
	header('Content-Type: application/json');
	echo json_encode(array("success" => setBlurb($blurb)));
}