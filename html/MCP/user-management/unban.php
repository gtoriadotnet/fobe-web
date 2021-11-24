<?php

/*
Alphaland 2021 
*/

if(!$user->isStaff()) {
    redirect("/");
}

//headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

$modmanager = new Alphaland\Moderation\UserModerationManager();

$data = json_decode(file_get_contents('php://input'));

$unban = false;
if ($data) {
	$username = $data->username;

	if($username) {
		$unban = $modmanager::UnbanUser(getID($username));
	}
}
die(json_encode(array("success" => $unban)));