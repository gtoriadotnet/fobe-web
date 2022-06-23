<?php

/*
Finobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");

$data = json_decode(file_get_contents('php://input'));

if (!$data)
{
	http_response_code(400);
}
else
{	
	$theme = $data->theme;
	header('Content-Type: application/json');
	echo json_encode(array("success" => setTheme($theme)));
}