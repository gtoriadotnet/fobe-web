<?php

/*
Fobe 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$assettype = $_GET['assetTypeId'];
$page = $_GET['page'];
$limit = $_GET['limit'];
$keyword = substr((string)$_GET['keyword'], 0, 32); //32 limit
$keywordq = '%'.$keyword.'%'; //query

//initial checks
if (!$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

//query
$query = "SELECT * FROM assets WHERE AssetTypeId = :i AND IsForSale = 1 AND IsModerated = 0 AND Name LIKE :u ORDER BY Updated DESC"; 

//count how many games without offset/limit
$catalogcount = $pdo->prepare($query);
$catalogcount->bindParam(':i', $assettype, PDO::PARAM_INT);
$catalogcount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$catalogcount->execute();
$catalogcount = $catalogcount->rowCount();

//data for pages
$total = $catalogcount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$catalog = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$catalog->bindParam(':i', $assettype, PDO::PARAM_INT);
$catalog->bindParam(':u', $keywordq, PDO::PARAM_STR);
$catalog->bindParam(':limit', $limit, PDO::PARAM_INT);
$catalog->bindParam(':offset', $offset, PDO::PARAM_INT);
$catalog->execute();

//final check to see if page is invalid 
if ($pages > 0)
{
	if ($page > $pages)
	{
		http_response_code(400);
	}
}

//construct the json array
$jsonData = array(
	"pageCount" => $pages,
	"pageResults" => (int)$catalog->rowCount(),
	"keyword" => $keyword
);

foreach($catalog as $item)
{
	$assetid = $item['id'];		
	$assetname = cleanOutput($item['Name']);
	$price = $item['PriceInAlphabux'];
	
	if ($price == 0)
		$price = "FREE!";
	
	$assetcreatorid = $item['CreatorId'];
	$creatorname = getUsername($assetcreatorid);
	$render = '';
	
	if($item['AssetTypeId'] != 3) {
		$render = getAssetRender($assetid);
	} else {
		$render = getImageFromAsset(1466); //1466 is default audio
	}
	
	$itemInfo = array(
		"id" => $assetid,
		"name" => $assetname,
		"price" => $price,
		"creatorName" => $creatorname,
		"creatorId" => $assetcreatorid,
		"thumbnail" => $render
	);
	
	array_push($jsonData, $itemInfo);
}
// ...

die(json_encode($jsonData));