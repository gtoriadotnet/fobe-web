<?php

/*
Fobe 2021 
*/

use Fobe\Web\WebContextManager;

if(!$user->IsStaff())
{
    WebContextManager::Redirect("/");
}

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$page = $_GET['page'];
$limit = $_GET['limit'];

//initial checks
if (!$limit || !$page)
{
	http_response_code(400);
}

if ($page < 1 || $limit < 1)
{
	http_response_code(400);
}

$assetscount = fetchPendingAssets()->rowCount();

//data for pages
$total = $assetscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$assets = fetchPendingAssets('LIMIT '.$limit.' OFFSET '.$offset);

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
	"pageResults" => (int)$assets->rowCount()
);

foreach($assets as $asset)
{
	$assetid = $asset['id'];
	$creatorid = $asset['CreatorId'];
	$assettypeid = $asset['AssetTypeId'];
	$name = cleanOutputNoFilter($asset['Name']);
	$desc = cleanOutputNoFilter($asset['Description']); //description of the game
	$creatorname = getUsername($creatorid); //creator of the game username
	$image = "";
	if ($assettypeid == 3) { //audio
		$image = getAssetFromAsset($assetid);
	}
	elseif ($assettypeid == 2|| $assettypeid == 11 || $assettypeid == 12) { //tshirts, shirts and pants
		$image = getSPTCosmeticTexture($assetid);
	} else {
		$image = getImageFromAsset($assetid); //anything else probably
	}
	
	$assetInfo = array(
		"assetid" => $assetid,
		"assettypeid" => $assettypeid,
		"name" => $name,
		"description" => $desc,
		"creatorname" => $creatorname,
		"creatorid" => $creatorid,
		"image" => $image
	);
	
	array_push($jsonData, $assetInfo);
}
// ...

die(json_encode($jsonData));