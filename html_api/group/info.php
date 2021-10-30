<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$groupid = (int)$_GET['id'];

if (!groupExists($groupid))
{
	die("{}");
}

//query
$query = "SELECT * FROM groups WHERE id = :i AND moderated = 0"; 

// Prepare the query
$group = $pdo->prepare($query);
$group->bindParam(":i", $groupid, PDO::PARAM_INT);
$group->execute();
$group = $group->fetch(PDO::FETCH_OBJ);

$id = $group->id;		
$name = cleanOutput($group->name);
$description = cleanOutput($group->description);
$manualapproval = boolval($group->manualapproval);
$creatorid = $group->creatorid;
$emblem = $group->emblem;
	
$itemInfo = array(
	array(
	"id" => $id,
	"name" => $name,
	"description" => $description,
	"members" => groupMemberCount($groupid),
	"creatorname" => getUsername($creatorid),
	"creatorid" => $creatorid,
	"manualJoinRequests" => isManualApproval($groupid),
	"pendingJoin" => isPendingRequest($groupid),
	"groupMember" => isGroupMember($user->id, $groupid),
	"groupOwner" => isGroupOwner($groupid),
	"configPermission" => configPermission($groupid),
	//"leavePermission" =>
	"wallViewPermission" => wallViewPermission($groupid),
	"wallPostPermission" => wallPostPermission($groupid),
	"wallDeletePermission" => wallDeletePermission($groupid),
	"postShoutPermission" => postShoutPermission($groupid),
	"manageLowerRankPermission" => manageLowerRankPermission($groupid),
	"kickLowerRankPermission" => kickLowerRankPermission($groupid),
	"acceptJoinRequestPermission" => acceptJoinRequestPermission($groupid),
	"viewAuditLogPermission" => viewAuditLogPermission($groupid),
	"emblem" => getAssetRender($emblem)
	)
);
// ...

die(json_encode($itemInfo));