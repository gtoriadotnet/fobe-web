<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$assetid = (int)$_GET['id'];
$updatesettings = (bool)$_GET['updatesettings'];
$rankuser = (bool)$_GET['rank'];
$removeuser = (bool)$_GET['remove'];
$whitelistuser = (bool)$_GET['whitelist'];
$removewhitelistuser = (bool)$_GET['unwhitelist'];

$data = json_decode(file_get_contents('php://input'));

if (!$data || !$assetid)
{
	http_response_code(400);
}
else
{
	$message = "";
	if ($updatesettings) 
	{
		$name = $data->Name;
		$description = $data->Description;
		$commentsenabled = (bool)$data->CommentsEnabled;
		$whitelistenabled = (bool)$data->WhitelistEnabled;
		$maxplayers = $data->MaxPlayers;
		$message = updatePBSGameSettings($assetid, $name, $description, $commentsenabled, $whitelistenabled, $maxplayers);
	}
	else if ($rankuser)
	{
		$userid = 0;

		if ($data->userid)
		{
			$userid = $data->userid;
		}
		else
		{
			$userid = getID($data->username);
		}

		if ($userid)
		{
			$rank = $data->rank;
			$message = updateBuildServerRank($assetid, $userid, $rank);
		}
		else
		{
			$message = "Invalid User";
		}
	}
	else if ($removeuser)
	{
		$userid = $data->userid;
		$message = removePBSUser($assetid, $userid);
	}
	else if ($whitelistuser)
	{
		$userid = getID($data->username);
		if ($userid)
		{
			$message = gameWhitelistAddUser($assetid, $userid);
		}
		else
		{
			$message = "Invalid User";
		}
	}
	else if ($removewhitelistuser)
	{
		$userid = $data->userid;
		$message = gameWhitelistRemoveUser($assetid, $userid);
	}

	if ($message === true) {
		$message = "PBS Updated";
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $message));
}