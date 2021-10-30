<?php

header("Cache-Control: no-cache, no-store");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

$userId = $_GET['userId'];
$otherUserIds = $_GET['otherUserIds'];

$users = "";
foreach ($otherUserIds as $id)
{
	if (friendsWithUser($userId, $id))
	{
		$users = $users . $id . ",";
	}	
}

//$userids = substr($users, 0, -1); //remove last comma pog
echo "," . $users;