<?php

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$category = $_GET['category'];
$creatorid = $_GET['creatorId'];
$page = $_GET['page'];
$limit = $_GET['limit'];
$keyword = substr((string)$_GET['keyword'], 0, 32); //32 limit
$keywordq = '%'.$keyword.'%'; //query

if (!$category)
{
	http_response_code(400);
}

if (!$creatorid)
{
	$creatorid = $user->id;
}

//initial checks
if (!$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//queries
if ($category == "FreeModels")
{
	$query = "SELECT * FROM assets WHERE AssetTypeId = 10 AND name LIKE :u AND isPublicDomain = 1 ORDER BY created DESC";
}
else if ($category == "UserModels")
{
	$query = "SELECT * FROM assets WHERE AssetTypeId = 10 AND name LIKE :u AND CreatorId = ".$creatorid." ORDER BY created DESC";
}
else 
{
	http_response_code(400);
}

//count how many shouts without offset/limit
$itemcount = $pdo->prepare($query);
$itemcount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$itemcount->execute();
$itemcount = $itemcount->rowCount();

//data for pages
$total = $itemcount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query 
$items = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$items->bindParam(':u', $keywordq, PDO::PARAM_STR);
$items->bindParam(':limit', $limit, PDO::PARAM_INT);
$items->bindParam(':offset', $offset, PDO::PARAM_INT);
$items->execute();

$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$items->rowCount()
);

foreach($items as $item)
{
	$itemAssetId = $item['id'];
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

die(json_encode($jsonData));