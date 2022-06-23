<?php

/*
Finobe 2021 
*/

//headers

use Finobe\Users\User;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");

$data = json_decode(file_get_contents('php://input'));

if (!$data)
{
	http_response_code(400);
}
else
{	
	$private = $data->preference;
	header('Content-Type: application/json');
	echo json_encode(array("success" => User::SetIsInventoryPrivate($user->id, $private)));
}