<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

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
	$updategroup = "";
	if ($updateinfo) //can be modified with configpermission (Should this be owner only?)(only general info)
	{
		$description = $data->description;
		$joinapprovals = (bool)$data->approvals;
		$img = $data->emblem;
		$updategroup = updateGeneralConfig($groupid, $description, $joinapprovals, $img);
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
		$updategroup = updateRole($groupid, $rank, $newrank, $name, $accessgroupwall, $postgroupwall, $deletegroupwallposts, $postgroupshout, $managelowerranks, $kicklowerranks, $acceptjoinrequests, $auditaccess);
	}
	else if ($newrole) //owner restricted
	{
		$name = $data->name;
		$rank = $data->rank;
		$updategroup = createRole($groupid, $name, $rank);
	}
	else if ($userrank) //manageLowerRankPermission needed
	{
		$userid = $data->userid;
		$rank = $data->rank;
		$updategroup = updateUserRank($groupid, $userid, $rank);
	}
	else if ($exileuser) //restricted to owner for now
	{
		$userid = $data->userid;
		$updategroup = exileUser($groupid, $userid);
	}
	else if ($approverequest) //restricted to owner for now
	{
		$userid = $data->userid;
		$updategroup = approveRequest($groupid, $userid);
	}
	else if ($denyrequest) //restricted to group owner for now
	{
		$userid = $data->userid;
		$updategroup = denyRequest($groupid, $userid);
	}
	else if ($deletepost) //requires delete permission
	{
		$postid = $data->postid;
		$updategroup = deletePost($postid, $groupid);
	}
	else
	{
		http_response_code(400);
	}
		
	if ($updategroup === true) {
		$updategroup = "Group Updated";
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $updategroup));
}