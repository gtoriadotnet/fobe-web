<?php

/*
Fobe 2021 
*/


//headers

use Fobe\Groups\Group;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$groupid = (int)$_GET['id'];
$updateinfo = (bool)$_GET['updateinfo'];
$updaterole = (bool)$_GET['updaterole'];
$newrole = (bool)$_GET['newrole'];
$userrank = (bool)$_GET['userrank'];
$exileuser = (bool)$_GET['exileuser'];
$approverequest = (bool)$_GET['approverequest'];
$denyrequest = (bool)$_GET['denyrequest'];
$deletepost = (bool)$_GET['deletepost'];

$data = json_decode(file_get_contents('php://input'));

if (!$data || !$groupid)
{
	http_response_code(400);
}
else
{
	$updategroup = false;
	if ($updateinfo) //can be modified with configpermission (Should this be owner only?)(only general info)
	{
		$description = $data->description;
		$joinapprovals = (bool)$data->approvals;
		$img = $data->emblem;

		try {
			if (Group::UpdateGeneralConfig($groupid, $description, $joinapprovals, $img)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($updaterole) //owner restricted
	{
		$rank = $data->rank;
		$newrank = $data->NewRank;
		$name = $data->Name;
		$accessgroupwall = $data->AccessGroupWall;
		$postgroupwall = $data->PostGroupWall;
		$deletegroupwallposts = $data->DeleteGroupWallPosts;
		$postgroupshout = $data->PostGroupShout;
		$managelowerranks = $data->ManageLowerRanks;
		$kicklowerranks = $data->KickLowerRanks;
		$acceptjoinrequests = $data->AcceptJoinRequests;
		$auditaccess = $data->ViewAuditLog;

		try {
			if (Group::UpdateRole($groupid, $rank, $newrank, $name, $accessgroupwall, $postgroupwall, $deletegroupwallposts, $postgroupshout, $managelowerranks, $kicklowerranks, $acceptjoinrequests, $auditaccess)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($newrole) //owner restricted
	{
		$name = $data->name;
		$rank = $data->rank;

		try {
			if (Group::CreateRole($groupid, $name, $rank)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($userrank) //manageLowerRankPermission needed
	{
		$userid = $data->userid;
		$rank = $data->rank;

		try {
			if (Group::UpdateUserRank($groupid, $userid, $rank)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($exileuser) //restricted to owner for now
	{
		$userid = $data->userid;

		try {
			if (Group::ExileUser($groupid, $userid)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($approverequest) //restricted to owner for now
	{
		$userid = $data->userid;
		try {
			if (Group::ApproveJoinRequest($groupid, $userid)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($denyrequest) //restricted to group owner for now
	{
		$userid = $data->userid;

		try {
			if (Group::DeleteJoinRequest($groupid, $userid)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else if ($deletepost) //requires delete permission
	{
		$postid = $data->postid;

		try {
			if (Group::DeletePost($postid, $groupid)) {
				$updategroup = true;
			}
		} catch (Exception $e) {
			$updategroup = $e->getMessage();
		}
	}
	else
	{
		http_response_code(400);
	}
		
	if ($updategroup === true) {
		$updategroup = "Group Updated";
	}
	echo json_encode(array("alert" => $updategroup));
}