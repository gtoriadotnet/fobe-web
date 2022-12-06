<?php


/*
Fobe 2021 
*/

//headers

use Fobe\Users\Render;
use Fobe\Users\User;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$angleRight = (bool)$_GET['angleRight'];
$angleLeft = (bool)$_GET['angleLeft'];

if ($angleRight && $angleLeft)
{
	die(http_response_code(401));
}
else
{
	$alert = false;

	if (!Render::PendingRender($user->id))
	{
		if ($angleRight) {
			if (User::SetHeadshotAngleRight($user->id)) {
				$alert = true;
			}
		} else if ($angleLeft) {
			if (User::SetHeadshotAngleLeft($user->id)) {
				$alert = true;
			}
		} else {
			if (User::SetHeadshotAngleCenter($user->id)) {
				$alert = true;
			}
		}

		if ($alert) {
			if (!Render::RenderCooldown($user->id)) {
				Render::RenderPlayer($localuser);
			} else {
				$alert = "Slow down!";
			}
		}
	}

	die(json_encode(array("result" => $alert)));
}