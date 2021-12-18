<?php

/*
Alphaland 2021 
*/

//headers

use Alphaland\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

if(!$user->isStaff())
{
    WebContextManager::Redirect("/");
}

//get params
$username = $_GET['username'];
$userid = getID($username);
$infractions = (bool)$_GET['infractions'];
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

//$query = "SELECT * FROM chat_logs WHERE whoSent = :who AND trippedFilter = :tf";
$query = "SELECT * FROM chat_logs WHERE message LIKE :u" . (!empty($userid) && is_int($userid)?" AND whoSent = ".$userid." ":" ") . " AND whoSent > 4 ORDER BY whenSent DESC"; 

//count how many games without offset/limit
$messagescount = $pdo->prepare($query);
$messagescount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$messagescount->execute();
$messagescount = $messagescount->rowCount();

//data for pages
$total = $messagescount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$messages = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$messages->bindParam(':u', $keywordq, PDO::PARAM_STR);
$messages->bindParam(':limit', $limit, PDO::PARAM_INT);
$messages->bindParam(':offset', $offset, PDO::PARAM_INT);
$messages->execute();

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
	"pageResults" => (int)$messages->rowCount()
);

foreach($messages as $message)
{
	$userid = $message['whoSent'];
	$gameAssetId = $message['gameAssetId'];
	$loggedMessage = cleanOutputNoFilter($message['message']);
	$whenlogged = date("m/d/Y", $message['whenSent']);
	$loggedMessages = array(
		"userid" => $userid,
		"username" => cleanOutputNoFilter(getUsername($userid)),
		"thumbnail" => getPlayerRender($userid),
		"placeName" =>  cleanOutputNoFilter(getAssetInfo($gameAssetId)->Name),
		"placeId" => $gameAssetId,
		"message" => $loggedMessage,
		"date" => $whenlogged
	);
	
	array_push($jsonData, $loggedMessages);
}
// ...

die(json_encode($jsonData));