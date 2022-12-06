<?php

use Fobe\Groups\Group;

//so stuff doesnt cache
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

$method = $_GET['method'];
$playerid = $_GET['playerid'];
$groupid = $_GET['groupid'];
$userid = $_GET['userid'];

if ($method == "IsFriendsWith") {
	header('Content-Type: text/xml');
	if (friendsWithUser($playerid, $userid) || $playerid == $userid) {
		echo '<Value Type="boolean">true</Value>';
	} else {
		echo '<Value Type="boolean">false</Value>';
	}
} elseif ($method == "IsInGroup") {
	header('Content-Type: text/xml');
	if (Group::IsMember($playerid, $groupid)) {
		echo '<Value Type="boolean">true</Value>';	
	} else {
		echo '<Value Type="boolean">false</Value>';	
	}
} elseif ($method == "GetGroupRank") {
	header('Content-Type: text/xml');
	if (Group::IsMember($playerid, $groupid)) {
		echo '<Value Type="integer">'.Group::GetRank($playerid, $groupid).'</Value>';	
	} else {
		echo '<Value Type="integer">0</Value>';	
	}
} elseif ($method == "GetGroupRole") {
	if (Group::IsMember($playerid, $groupid)) {
		header('Content-Type: text/xml');
		echo Group::GetUserRankName($playerid, $groupid);	
	}
}