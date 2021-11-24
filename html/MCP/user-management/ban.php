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
				$ban = $modmanager::BanUser($userid, $reason, $expiration, 0); //0 warning type
				break;
			case "temp":
				$ban = $modmanager::BanUser($userid, $reason, $expiration, 1); //1 temp type
				break;
			case "perm":
				$ban = $modmanager::BanUser($userid, $reason, $expiration, 2); //2 perm type
				break;
			case "poison":
				$ban = $modmanager::PoisonBan($userid, $reason);
				break;
			case "limb":
				$ban = $modmanager::ReferralLimbBan($userid, $reason);
				break;
			default:
				break;		
		}
	}
}
die(json_encode(array("success" => $ban)));