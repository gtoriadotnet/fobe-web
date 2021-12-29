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
$rank = (int)$_GET['rank'];
$exclude = (int)$_GET['excluderank'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$groupid || !$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

if (!empty($rank) && is_int($rank) && !empty($exclude) && is_int($exclude))
{
	http_response_code(400);
}

//cool query? single line conditions are pog
$query = "SELECT * FROM group_members WHERE groupid = :gid" . (!empty($rank) && is_int($rank)?" AND rank = ".$rank." ":" ") . (!empty($exclude) && is_int($exclude)?" AND rank < ".$exclude." ":" ") ."ORDER BY rank DESC"; 

//count how many shouts without offset/limit
$membercount = $pdo->prepare($query);
$membercount->bindParam(":gid", $groupid, PDO::PARAM_INT);
$membercount->execute();
$membercount = $membercount->rowCount();

//data for pages
$total = $membercount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$members = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$members->bindParam(":gid", $groupid, PDO::PARAM_INT);
$members->bindParam(':limit', $limit, PDO::PARAM_INT);
$members->bindParam(':offset', $offset, PDO::PARAM_INT);
$members->execute();

$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$members->rowCount()
);

foreach($members as $member)
{
	$userid = $member['userid']; //id of the game
	$username = getUsername($userid);
	$thumbnail = getPlayerRender($userid);
	$rank = $member['rank']; //players in the game	
	
	$membersInfo = array(
		"groupid" => $groupid,
		"username" => $username,
		"userid" => $userid,
		"thumbnail" => $thumbnail,
		"rankname" => Group::GetRankName($rank, $groupid),
		"rank" => $rank	
	);
	array_push($jsonData, $membersInfo);
}
// ...

die(json_encode($jsonData));