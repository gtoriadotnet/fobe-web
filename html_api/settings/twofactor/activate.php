<?php

/*
Alphaland 2021 
*/

//headers

use Alphaland\Users\TwoFactor;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

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
	echo json_encode(array("success" => TwoFactor::ActivateUser2FA($userid, $code)));
}