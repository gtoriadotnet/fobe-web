<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$angleRight = (bool)$_GET['angleRight'];
$angleLeft = (bool)$_GET['angleLeft'];

if ($angleRight && $angleLeft)
{
	die(http_response_code(400));
}
else
{
	$alert = false;

	if (!checkUserPendingRender($user->id))
	{
		if ($angleRight) {
			if (setHeadshotAngleRight($user->id)) {
				$alert = true;
			}
		} else if ($angleLeft) {
			if (setHeadshotAngleLeft($user->id)) {
				$alert = true;
			}
		} else {
			if (setHeadshotAngleCenter($user->id)) {
				$alert = true;
			}
		}

		if ($alert) {
			if (!isRenderCooldown($user->id)) {
				rerenderutility();
			} else {
				$alert = "Slow down!";
			}
		}
	}

	die(json_encode(array("result" => $alert)));
}