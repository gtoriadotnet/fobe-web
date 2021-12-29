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
$query = "SELECT * FROM groups WHERE name LIKE :u ORDER BY (SELECT COUNT(*) FROM group_members WHERE groupid = groups.id) DESC"; 

//count how many groups without offset/limit
$groupscount = $pdo->prepare($query);
$groupscount->bindParam(':u', $keywordq, PDO::PARAM_STR);
$groupscount->execute();
$groupscount = $groupscount->rowCount();

//data for pages
$total = $groupscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$groups = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$groups->bindParam(':u', $keywordq, PDO::PARAM_STR);
$groups->bindParam(':limit', $limit, PDO::PARAM_INT);
$groups->bindParam(':offset', $offset, PDO::PARAM_INT);
$groups->execute();

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
	"pageResults" => (int)$groups->rowCount(),
	"keyword" => $keyword
);

foreach($groups as $group)
{
	$groupid = $group['id'];
	$name = cleanOutput($group['name']); 
	$desc = cleanOutput($group['description']); 
	$members = Group::MemberCount($groupid);
	$creatorid = $group['creatorid']; 
	$emblem = $group['emblem']; 
	
	$groupsInfo = array(
		"id" => $groupid,
		"name" => $name,
		"description" => $desc,
		"members" => $members,
		"creatorid" => $creatorid,
		"emblem" => getAssetRender($emblem)
	);
	
	array_push($jsonData, $groupsInfo);
}
// ...

die(json_encode($jsonData));