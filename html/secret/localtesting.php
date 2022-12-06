<?php

use Fobe\Common\Signing;

$user = $_GET['user'];

if ($user == 1)
{
	$joinparams = json_encode(array(
		"ClientPort" => 0,
		"MachineAddress" => "localhost",
		"ServerPort" => "65535",
		"PingUrl" => "",
		"PingInterval" => 45,
		"UserName" => "Astrologies",
		"SeleniumTestMode" => false, //always false, dont need this
		"UserId" => 2,
		"SuperSafeChat" => false, //always false, dont need this
		"CharacterAppearance" => "https://api.idk16.xyz/users/avatar-accoutrements?userId=2",
		"ClientTicket" => "111",
		"GameId" => '00000000-0000-0000-0000-000000000000', //not set rn?
		"PlaceId" => 186,
		"BaseUrl" => $url . "/",
		"ChatStyle" => "ClassicAndBubble", //TODO: make an option for this
		"VendorId" => 0, //0, dont need this rn?
		"ScreenShotInfo" => "", //blank, dont need this rn?
		"VideoInfo" => "", //blank, dont need this rn?
		"CreatorId" => 2,
		"CreatorTypeEnum" => "User", //only player places, dont need this
		"MembershipType" => "None", //no memberships rn
		"AccountAge" => 469,
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
elseif ($user == 2)
{
	$joinparams = json_encode(array(
		"ClientPort" => 0,
		"MachineAddress" => "localhost",
		"ServerPort" => "65535",
		"PingUrl" => "",
		"PingInterval" => 45,
		"UserName" => "Astrologies2",
		"SeleniumTestMode" => false, //always false, dont need this
		"UserId" => 3,
		"SuperSafeChat" => false, //always false, dont need this
		"CharacterAppearance" => "https://api.idk16.xyz/users/avatar-accoutrements?userId=2",
		"ClientTicket" => "111",
		"GameId" => '00000000-0000-0000-0000-000000000000', //not set rn?
		"PlaceId" => 186,
		"BaseUrl" => $url . "/",
		"ChatStyle" => "ClassicAndBubble", //TODO: make an option for this
		"VendorId" => 0, //0, dont need this rn?
		"ScreenShotInfo" => "", //blank, dont need this rn?
		"VideoInfo" => "", //blank, dont need this rn?
		"CreatorId" => 2,
		"CreatorTypeEnum" => "User", //only player places, dont need this
		"MembershipType" => "None", //no memberships rn
		"AccountAge" => 469,
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
elseif ($user == 3)
{
	$joinparams = json_encode(array(
		"ClientPort" => 0,
		"MachineAddress" => "localhost",
		"ServerPort" => "65535",
		"PingUrl" => "",
		"PingInterval" => 45,
		"UserName" => "Astrologies3",
		"SeleniumTestMode" => false, //always false, dont need this
		"UserId" => 4,
		"SuperSafeChat" => false, //always false, dont need this
		"CharacterAppearance" => "https://api.idk16.xyz/users/avatar-accoutrements?userId=2",
		"ClientTicket" => "111",
		"GameId" => '00000000-0000-0000-0000-000000000000', //not set rn?
		"PlaceId" => 186,
		"BaseUrl" => $url . "/",
		"ChatStyle" => "ClassicAndBubble", //TODO: make an option for this
		"VendorId" => 0, //0, dont need this rn?
		"ScreenShotInfo" => "", //blank, dont need this rn?
		"VideoInfo" => "", //blank, dont need this rn?
		"CreatorId" => 2,
		"CreatorTypeEnum" => "User", //only player places, dont need this
		"MembershipType" => "None", //no memberships rn
		"AccountAge" => 469,
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
else
{
	die('<h1><b>O_O</b></h1>');
}

//echo signData($script);