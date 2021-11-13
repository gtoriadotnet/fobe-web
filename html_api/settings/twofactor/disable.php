<?php

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userid = $user->id;

//feature tester locked
if (!inFeatureTesterGroup($userid)) {
	die(http_response_code(401));
}

echo json_encode(array("success" => deleteUser2FA($userid)));