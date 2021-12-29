<?php

/*
Alphaland 2021 
*/


//headers

use Alphaland\Groups\Group;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$groupid = (int)$_GET['id'];

if (!Group::Exists($groupid))
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
	"members" => Group::MemberCount($groupid),
	"creatorname" => getUsername($creatorid),
	"creatorid" => $creatorid,
	"manualJoinRequests" => Group::IsManualApproval($groupid),
	"pendingJoin" => Group::IsPendingRequest($user->id, $groupid),
	"groupMember" => Group::IsMember($user->id, $groupid),
	"groupOwner" => Group::IsOwner($user->id, $groupid),
	"configPermission" => Group::ConfigPermission($user->id, $groupid),
	//"leavePermission" =>
	"wallViewPermission" => Group::WallViewPermission($user->id, $groupid),
	"wallPostPermission" => Group::WallPostPermission($user->id, $groupid),
	"wallDeletePermission" => Group::WallDeletePermission($user->id, $groupid),
	"postShoutPermission" => Group::PostShoutPermission($user->id, $groupid),
	"manageLowerRankPermission" => Group::ManageLowerRankPermission($user->id, $groupid),
	"kickLowerRankPermission" => Group::KickLowerRankPermission($user->id, $groupid),
	"acceptJoinRequestPermission" => Group::AcceptJoinRequestPermission($user->id, $groupid),
	"viewAuditLogPermission" => Group::ViewAuditLogPermission($user->id, $groupid),
	"emblem" => getAssetRender($emblem)
	)
);
// ...

die(json_encode($itemInfo));