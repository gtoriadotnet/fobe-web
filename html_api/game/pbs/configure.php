<?php


/*
Fobe 2021 
*/

//headers

use Fobe\Games\Game;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

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
		$message = null;
		if (Game::RemovePersonalBuildServerRank($assetid, $userid)) {
			$message = true;
		}
	}
	else if ($whitelistuser)
	{
		$userid = getID($data->username);
		if ($userid)
		{
			try {
				if (Game::WhitelistAddUser($assetid, $userid)) {
					$message = true;
				}
			} catch (Exception $e) {
				$message = $e->getMessage();
			}
		}
		else
		{
			$message = "User not found";
		}
	}
	else if ($removewhitelistuser)
	{
		$userid = $data->userid;
		try {
			if (Game::WhitelistRemoveUser($assetid, $userid)) {
				$message = true;
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
	}

	if ($message === true) {
		$message = "PBS Updated";
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $message));
}