<?php

/*
	Alphaland 2021
	Very messy but will clean up
*/

use Alphaland\Common\Signing;
use Alphaland\Games\Game;
use Alphaland\Games\Ticket;

header("Content-Type: text/plain");

$token = (string)$_GET['ticket'];
$local = $_GET['local'];

if ($local)
{
		$joinparams = json_encode(array(
		"MachineAddress" => "localhost",
		"ServerPort" => "65535",
		"ClientPort" => 0,
		"UserName" => "Astrologies",
		"UserId" => 2,
		"CreatorId" => 2,
		"CreatorTypeEnum" => "User",
		"ChatStyle" => "ClassicAndBubble",
		"PlaceId" => 186,
		"CharacterAppearance" => "https://api.alphaland.cc/users/avatar-accoutrements?userId=2",
		//"IsRobloxPlace" => true,
		"ClientTicket" => "111",
		"BaseUrl" => $url . "/",
		"PingUrl" => $url . "",
		"PingInterval" => 45
	), JSON_UNESCAPED_SLASHES);
	
	die(Signing::SignData($joinparams));
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

		if (userAccessToGame($gameInfo->id, $sInfo->uid))
		{
			$jobid = $sInfo->jobid;
			$placeid = $gameInfo->id;
			$userid = $sInfo->uid;
			$username = getUsername($userid);
			$characterappearance = "https://api.alphaland.cc/users/avatar-accoutrements?userId=" . $userid;
			$accountage = round((time()-userInfo($userid)->joindate)/86400);

			$joinparams = json_encode(array(
				"ClientPort" => 0,
				"MachineAddress" => $serverInfo->ip,
				"ServerPort" => $serverInfo->port,
				"PingUrl" => $url . "/Game/ClientPing.ashx?UserID=" . $userid . "&PlaceID=" . $placeid,
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
			
			die(Signing::SignData($joinparams));
		}
	} 
}
die(http_response_code(401));