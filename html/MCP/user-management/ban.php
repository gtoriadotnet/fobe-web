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

$data = json_decode(file_get_contents('php://input'));

$ban = false;
if ($data) {
	$username = $data->username;
	$reason = $data->reason;  
	$expiration = $data->expiration;
	$type = $data->type;

	if($username && $reason && $type) {
		$userid = getID($username);
		$reason = cleanInput($reason);
		switch ($type) {
			case "warn":
				$ban = UserModerationManager::BanUser($userid, $reason, $expiration, 0); //0 warning type
				break;
			case "temp":
				$ban = UserModerationManager::BanUser($userid, $reason, $expiration, 1); //1 temp type
				break;
			case "perm":
				$ban = UserModerationManager::BanUser($userid, $reason, $expiration, 2); //2 perm type
				break;
			case "poison":
				$ban = UserModerationManager::PoisonBan($userid, $reason);
				break;
			case "limb":
				$ban = UserModerationManager::ReferralLimbBan($userid, $reason);
				break;
			default:
				break;		
		}
	}
}
die(json_encode(array("success" => $ban)));