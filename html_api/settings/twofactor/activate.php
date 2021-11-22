<?php

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

$twofactor = new Alphaland\Users\TwoFactor();
$userid = $user->id;

$data = json_decode(file_get_contents('php://input'));

if (!$data)
{
	http_response_code(400);
}
else
{	
	$code = $data->code;
	header('Content-Type: application/json');
	echo json_encode(array("success" => $twofactor::activateUser2FA($userid, $code)));
}