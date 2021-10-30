<?php


/*
Alphaland 2021 
TODO: UNGHETTO
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];
$assettypeid = $_GET['assetTypeId'];
$page = $_GET['page'];
$limit = $_GET['limit'];
$keyword = substr((string)$_GET['keyword'], 0, 32); //32 limit
$keywordq = '%'.$keyword.'%'; //query

if (!$userid)
{
	$userid = $user->id;
}

if (!userExists($userid))
{
	http_response_code(400);
	die();
}

//initial checks
if (!$limit || !$page || !$assettypeid)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//query GHETTO CUZ BINDPARAM SUCKS
$query = 'SELECT * FROM owned_assets WHERE uid = :uid AND aid IN (SELECT id FROM assets WHERE id = owned_assets.aid AND AssetTypeId = ' . $assettypeid . ' AND Name LIKE "'.$keywordq.'") ORDER BY when_sold DESC'; 

//count how many shouts without offset/limit
$itemcount = $pdo->prepare($query);
$itemcount->bindParam(":uid", $userid, PDO::PARAM_INT);
$itemcount->execute();
$itemcount = $itemcount->rowCount();

//data for pages
$total = $itemcount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$items = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$items->bindParam(":uid", $userid, PDO::PARAM_INT);
$items->bindParam(':limit', $limit, PDO::PARAM_INT);
$items->bindParam(':offset', $offset, PDO::PARAM_INT);
$items->execute();

$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$items->rowCount()
);

foreach($items as $item)
{
	$itemAssetId = $item['aid'];
	$itemInfo = getAssetInfo($itemAssetId);
	$name = cleanOutput($itemInfo->Name);
	$creatorid = $itemInfo->CreatorId;
	$creator = getUsername($creatorid);
	$render = getAssetRender($itemAssetId);
					
	$items = array(
		"id" => $itemAssetId,
		"name" => $name,
		"creatorId" => $creatorid,
		"creator" => $creator,
		"thumbnail" => $render
	);		

	array_push($jsonData, $items);	
}
// ...

if (!isUserInventoryPrivate($userid)) {
	die(json_encode($jsonData));
} else {
	die(json_encode(["message"=>"User's inventory is private"]));
}
