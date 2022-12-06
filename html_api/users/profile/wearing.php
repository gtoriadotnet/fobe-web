<?php


/*
Fobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $_GET['userId'];

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
$query = 'SELECT * FROM wearing_items WHERE uid = :uid ORDER BY `id` DESC'; 

// Prepare the paged query 
$items = $pdo->prepare($query);
$items->bindParam(":uid", $userid, PDO::PARAM_INT);
$items->execute();

$jsonData = array(
	"username" => cleanOutput(getUsername($userid)),
	"userid" => (int)$userid,
	"itemCount" => (int)$items->rowCount()
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

die(json_encode($jsonData));