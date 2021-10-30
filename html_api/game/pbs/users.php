<?php


/*
	Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$assetid = (int)$_GET['id'];
$whitelist = (bool)$_GET['whitelist'];
$rank = (int)$_GET['rank'];
$nobanned = (bool)$_GET['nobanned'];
$exclude = (int)$_GET['excluderank'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$assetid || !$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

if (!$whitelist)
{
	if (!empty($rank) && is_int($rank) && !empty($exclude) && is_int($exclude))
	{
		http_response_code(400);
	}
}

if (!$whitelist) //not whitelist query
{
	//cool query? single line conditions are pog
	$query = "SELECT * FROM personal_build_ranks WHERE placeid = :pid" . (!empty($rank) && is_int($rank)?" AND rank = ".$rank." ":" ") . (!empty($exclude) && is_int($exclude)?" AND rank < ".$exclude." ":" AND rank < 255 ") . ($nobanned?" AND rank != 0 ":" ") . "AND rank != 10 ORDER BY whenRanked DESC"; 
}
else
{
	$query = "SELECT * FROM game_access WHERE placeid = :pid ORDER BY whenWhitelisted DESC"; 
}

//count how many users without offset/limit
$userscount = $pdo->prepare($query);
$userscount->bindParam(":pid", $assetid, PDO::PARAM_INT);
$userscount->execute();
$userscount = $userscount->rowCount();

//data for pages
$total = $userscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$members = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$members->bindParam(":pid", $assetid, PDO::PARAM_INT);
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
	
	if (!$whitelist)
	{
		$membersInfo = array(
			"username" => $username,
			"userid" => $userid,
			"thumbnail" => $thumbnail,
			"rankname" => getPBSRankName($rank),
			"rank" => $rank	
		);
	}
	else 
	{
		$membersInfo = array(
			"username" => $username,
			"userid" => $userid,
			"thumbnail" => $thumbnail
		);
	}
	array_push($jsonData, $membersInfo);
}
// ...

die(json_encode($jsonData));