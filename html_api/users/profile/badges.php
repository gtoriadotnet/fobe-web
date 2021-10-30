<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];
$official = $_GET['official'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$limit || !$page || !$userid)
{
	http_response_code(400);
	die();
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
	die();
}

$query = "";
if ($official) {
	$query = "SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 1";
	$playerbadges = officialPlayerBadges($userid);
} else {
	$query = "SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 0";
	$playerbadges = getPlayerBadges($userid);
}

//count how many badges without offset/limit
$badgescount = $pdo->prepare($query);
$badgescount->bindParam(":i", $userid, PDO::PARAM_INT);
$badgescount->execute();
$badgescount = $badgescount->rowCount();

//data for pages
$total = $badgescount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$badges = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$badges->bindParam(":i", $userid, PDO::PARAM_INT);
$badges->bindParam(':limit', $limit, PDO::PARAM_INT);
$badges->bindParam(':offset', $offset, PDO::PARAM_INT);
$badges->execute();

//final check to see if page is invalid 
if ($pages > 0)
{
	if ($page > $pages)
	{
		http_response_code(400);
	}
}
	
$userBadges = array (
	"pageCount" => $pages,
	"pageResults" => (int)$badges->rowCount()
);

foreach($badges as $badges)
{
	if ($official) {
		$badgeinfo = officialBadgeInfo($badges['bid']);
		$badgename = $badgeinfo->name;
		$badgedescription = $badgeinfo->description;
		$badgeimage = $badgeinfo->image;
	} else {
		$badgeinfo = getUserBadgeInfo($badges['bid']);
		$badgename = $badgeinfo->Name;
		$badgedescription = $badgeinfo->Description;
		$badgeimage = getUserBadgeImage($badges['bid']);
	}
					
	$badges = array(
		"badgeId" => $badges['bid'],
		"badgeName" => $badgename,
		"badgeDescription" => $badgedescription,
		"badgeImage" => $badgeimage
	);		

	array_push($userBadges, $badges);	
}
// ...

die(json_encode($userBadges));