<?php

/*
	Finobe 2021
	Very messy but will clean up
*/

use Finobe\Common\Signing;
use Finobe\Games\Game;
use Finobe\Games\Ticket;

header("Content-Type: text/plain");

$token = (string)$_GET['ticket'];
$local = $_GET['local'];

if ($local)
{
	$userid = 2;
	$accountage = 1337;
	$username = "Raymonf";
	$characterappearance = "https://api.idk16.xyz/users/avatar-accoutrements?userId=" . $userid;
	$jobid = "Test";
	$joinparams = json_encode(array(
		"ClientPort" => 0,
		"MachineAddress" => "127.0.0.1",
		"ServerPort" => "65535",
		"PingUrl" => "",
		"PingInterval" => 45,
		"UserName" => $username,
		"SeleniumTestMode" => false, //always false, dont need this
		"UserId" => $userid,
		"SuperSafeChat" => false, //always false, dont need this
		"CharacterAppearance" => $characterappearance,
		"ClientTicket" => Ticket::ClientTicket(array(
			$userid,
			$accountage,
			$username,
			$characterappearance,
			$jobid
		)),
		"GameId" => '00000000-0000-0000-0000-000000000000', //not set rn?
		"PlaceId" => 0,
		"BaseUrl" => $url . "/",
		"ChatStyle" => "ClassicAndBubble", //TODO: make an option for this
		"VendorId" => 0, //0, dont need this rn?
		"ScreenShotInfo" => "", //blank, dont need this rn?
		"VideoInfo" => "", //blank, dont need this rn?
		"CreatorId" => 2,
		"CreatorTypeEnum" => "User", //only player places, dont need this
		"MembershipType" => "None", //no memberships rn
		"AccountAge" => $accountage,
		"CookieStoreEnabled" => false, //always false, dont need this
		"IsRobloxPlace" => false, //dont this this rn?
		"GenerateTeleportJoin" => false, //dont need this rn?
		"IsUnknownOrUnder13" => false, //dont need this rn?
		"SessionId" => "", //blank, dont need this rn?
		"DataCenterId" => 0, //0, dont need this rn?
		"UniverseId" => 0, //0, dont need this rn?
		"BrowserTrackerId" => "" //blank, dont need this rn?
	), JSON_UNESCAPED_SLASHES);
	
	die(Signing::SignData("\r\n".$joinparams));
}

if ($_SERVER['HTTP_USER_AGENT'] == $GLOBALS['clientUserAgent']) //user agent restricted
{
	$q = $pdo->prepare("SELECT * FROM game_launch_tokens WHERE token = :i AND (whenCreated + 20) > UNIX_TIMESTAMP()");
	$q->bindParam(":i", $token, PDO::PARAM_STR);
	$q->execute();
	if($q->rowCount() > 0) 
	{
		$sInfo = $q->fetch(PDO::FETCH_OBJ);
		
		$siq = $pdo->prepare("SELECT * FROM open_servers WHERE jobid = :i");
		$siq->bindParam(":i", $sInfo->jobid, PDO::PARAM_INT);
		$siq->execute();
		$serverInfo = $siq->fetch(PDO::FETCH_OBJ);

		$gameInfo = getAssetInfo($serverInfo->gameID);

		if (Game::UserAccess($gameInfo->id, $sInfo->uid))
		{
			$jobid = $sInfo->jobid;
			$placeid = $gameInfo->id;
			$userid = $sInfo->uid;
			$username = getUsername($userid);
			$characterappearance = "https://api.idk16.xyz/users/avatar-accoutrements?userId=" . $userid;
			$accountage = round((time()-userInfo($userid)->joindate)/86400);

			$joinparams = json_encode(array(
				"ClientPort" => 0,
				"MachineAddress" => $serverInfo->ip,
				"ServerPort" => $serverInfo->port,
				"PingUrl" => $url . "/Game/ClientPing?UserID=" . $userid . "&PlaceID=" . $placeid,
				"PingInterval" => 45,
				"UserName" => $username,
				"SeleniumTestMode" => false, //always false, dont need this
				"UserId" => $userid,
				"SuperSafeChat" => false, //always false, dont need this
				"CharacterAppearance" => $characterappearance,
				"ClientTicket" => Ticket::ClientTicket(array(
					$userid,
					$accountage,
					$username,
					$characterappearance,
					$jobid
				)),
				"GameId" => '00000000-0000-0000-0000-000000000000', //not set rn?
				"PlaceId" => $placeid,
				"BaseUrl" => $url . "/",
				"ChatStyle" => Game::ConvertChatStyle(Game::GetChatStyle($placeid)), //TODO: make an option for this
				"VendorId" => 0, //0, dont need this rn?
				"ScreenShotInfo" => "", //blank, dont need this rn?
				"VideoInfo" => "", //blank, dont need this rn?
				"CreatorId" => $gameInfo->CreatorId,
				"CreatorTypeEnum" => "User", //only player places, dont need this
				"MembershipType" => "None", //no memberships rn
				"AccountAge" => $accountage,
				"CookieStoreEnabled" => false, //always false, dont need this
				"IsRobloxPlace" => false, //dont this this rn?
				"GenerateTeleportJoin" => false, //dont need this rn?
				"IsUnknownOrUnder13" => false, //dont need this rn?
				"SessionId" => "", //blank, dont need this rn?
				"DataCenterId" => 0, //0, dont need this rn?
				"UniverseId" => 0, //0, dont need this rn?
				"BrowserTrackerId" => "" //blank, dont need this rn?
			), JSON_UNESCAPED_SLASHES);
			
			die(Signing::SignData("\r\n".$joinparams));
		}
	} 
}
die(http_response_code(401));