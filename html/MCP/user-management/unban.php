<?php

/*
Alphaland 2021 
*/

use Alphaland\Moderation\UserModerationManager;

if(!$user->isStaff()) {
    redirect("/");
}

//headers
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

// if it's static you don't need to instantiate the class

$data = json_decode(file_get_contents('php://input'));

$unban = false;
if ($data) {
	$username = $data->username;

	if($username) {
		$unban = UserModerationManager::UnbanUser(getID($username));
	}
}
die(json_encode(array("success" => $unban)));