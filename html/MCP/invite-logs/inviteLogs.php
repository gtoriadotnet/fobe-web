<?php

use Fobe\Web\WebContextManager;

if(!$user->IsStaff())
{
    WebContextManager::Redirect("/");
}

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
header('Content-Type: application/json');

$b = $pdo->prepare("SELECT * FROM `users_invited` ORDER BY whenAccepted DESC");
$b->execute();

$jsonData = array();

foreach($b as $inviteInfo)
{
	$invitedUser = $inviteInfo['invitedUser'];
	$whoInvited = $inviteInfo['whoInvited'];
	$whenAccepted = $inviteInfo['whenAccepted'];
	
	$jsonInfo = array(
		"invitedUser" => $invitedUser,
		"invitedUsername" => cleanOutput(getUsername($invitedUser)),
		"whoInvited" => $whoInvited,
		"whoInvitedUsername" => cleanOutput(getUsername($whoInvited)),
		"whenAccepted" =>  date("m/d/Y", $whenAccepted)
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));