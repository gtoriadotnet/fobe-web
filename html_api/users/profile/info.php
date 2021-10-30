<?php


/*
Alphaland 2021
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];

//initial checks
if (!$userid)
{
	$userid = $user->id;
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

//user info
$userquery = $pdo->prepare('SELECT * FROM `users` WHERE id = :uid');
$userquery->bindParam(':uid', $userid, PDO::PARAM_INT);
$userquery->execute();
$userquery = $userquery->fetch(PDO::FETCH_OBJ);

$username = getUsername($userquery->id);
$usershout = userShout($userquery->id);
$blurb = cleanOutput($userquery->blurb);
$joindate = date("m/d/Y", $userquery->joindate);
$placevisits = userPlaceVisits($userquery->id); 
$privateinventory = isUserInventoryPrivate($userquery->id);
$playerender = getPlayerRender($userquery->id);
$playingInfo = userPlaying($userquery->id);

$userInfo = array (
	array(
		"userid" => $userquery->id,
		"siteStatus" => siteStatus($userquery->id),
		"gameAssetId" => $playingInfo['placeid'],
		"gameJobId" => $playingInfo['jobid'],
		"username" => $username,
		"shout" => $usershout,
		"blurb" => $blurb,
		"joindate" => $joindate,
		"placevisits" => $placevisits,
		"privateInventory" => $privateinventory,
		"friendsStatus" => friendStatus($userquery->id),
		"thumbnail" => $playerender
	)
);
// ...

die(json_encode($userInfo));