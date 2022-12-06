<?php

/*
Fobe 2021 
*/


//headers

use Fobe\Groups\Group;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$groupid = (int)$_GET['id'];
$rank = (int)$_GET['rank'];
$exclude = (int)$_GET['excluderank'];



//query
$query = "SELECT * FROM group_roles WHERE groupid = :gid" . (!empty($rank) && is_int($rank)?" AND rank = ".$rank." ":" ") . (!empty($exclude) && is_int($exclude)?" AND rank < ".$exclude." ":" " . "ORDER BY rank DESC"); 

// Prepare the query
$roles = $pdo->prepare($query);
$roles->bindParam(":gid", $groupid, PDO::PARAM_INT);
$roles->execute();

$jsonData = array();

foreach($roles as $role)
{
	$rolename = cleanOutput($role['rolename']);
	$rolerank = (int)$role['rank'];
	$accessgroupwall = (bool)$role['AccessGroupWall'];
	$postgroupwall = (bool)$role['PostGroupWall'];
	$deletegroupwall = (bool)$role['DeleteGroupWallPosts'];
	$postgroupshout = (bool)$role['PostGroupShout'];
	$managelowerranks = (bool)$role['ManageLowerRanks'];
	$kicklowerranks = (bool)$role['KickLowerRanks'];
	$acceptjoinrequests = (bool)$role['AcceptJoinRequests'];
	$viewauditlog = (bool)$role['ViewAuditLog'];
	
	$roleInfo = array(
		"name" => $rolename,
		"members" => Group::RankMemberCount($groupid, $rolerank),
		"rank" => $rolerank,
		"wallViewPermission" => $accessgroupwall,
		"wallPostPermission" => $postgroupwall,
		"wallDeletePermission" => $deletegroupwall,
		"postShoutPermission" => $postgroupshout,
		"manageLowerRankPermission" => $managelowerranks,
		"kickLowerRankPermission" => $kicklowerranks,
		"acceptJoinRequestPermission" => $acceptjoinrequests,
		"viewAuditLogPermission" => $viewauditlog
	);
	
	array_push($jsonData, $roleInfo);
}
// ...

die(json_encode($jsonData));