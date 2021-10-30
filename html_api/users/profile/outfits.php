<?php


/*
Alphaland 2021 
GHETTO GHETTO GHETTO JUST WANT MORE STUFF HANDLED WITH JS TODO: UNGHETTO
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];
$page = $_GET['page'];
$limit = $_GET['limit'];

if (!$userid)
{
	$userid = $user->id;
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

//query
$query = 'SELECT * FROM user_outfits WHERE userid = :uid ORDER BY whenCreated DESC'; 

//count how many shouts without offset/limit
$outfitcount = $pdo->prepare($query);
$outfitcount->bindParam(":uid", $userid, PDO::PARAM_INT);
$outfitcount->execute();
$outfitcount = $outfitcount->rowCount();

//data for pages
$total = $outfitcount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$outfits = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$outfits->bindParam(":uid", $userid, PDO::PARAM_INT);
$outfits->bindParam(':limit', $limit, PDO::PARAM_INT);
$outfits->bindParam(':offset', $offset, PDO::PARAM_INT);
$outfits->execute();

$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$outfits->rowCount()
);

foreach($outfits as $outfit)
{
	$name = cleanOutput($outfit['name']);
	$thumbhash = $outfit['ThumbHash'];
				
	$outfitData = array(
		"id" => $outfit['id'],
		"userid" => $userid,
		"name" => $name,
		"thumbnail" => $GLOBALS['renderCDN'] . "/" . $thumbhash //kinda ghetto
	);		

	array_push($jsonData, $outfitData);	
}
// ...

die(json_encode($jsonData));