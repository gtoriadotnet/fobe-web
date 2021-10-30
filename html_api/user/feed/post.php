<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

$shout = cleanInput(json_decode(file_get_contents('php://input'))->shout);

$newshout = setShout($shout);
if ($newshout === true) {
	$newshout = "Shout Posted";
}
header('Content-Type: application/json');
echo json_encode(array("alert" => $newshout));