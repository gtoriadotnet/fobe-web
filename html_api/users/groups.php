<?php

/*
Alphaland 2021 
*/


//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userid = (int)$_GET['userId'];

if (!$userid)
{
	$userid = $user->id;
}

//get params
$page = $_GET['page'];
$limit = $_GET['limit'];
$keyword = substr((string)$_GET['keyword'], 0, 32); //32 limit
$keywordq = '%'.$keyword.'%'; //query

//query
$query = "SELECT * FROM groups WHERE EXISTS (SELECT * FROM group_members WHERE userid = :uid AND groupid = groups.id)";

$jsonData = array();

if ($limit || $page)
{
	if ($page == 0)
	{
		$page = 1;
	}
	
	$query .= ' AND name LIKE :u';
	//count how many games without offset/limit
	$groupscount = $pdo->prepare($query);
	$groupscount->bindParam(':uid', $userid, PDO::PARAM_INT);
	$groupscount->bindParam(':u', $keywordq, PDO::PARAM_STR);
	$groupscount->execute();
	$groupscount = $groupscount->rowCount();

	//data for pages
	$total = $groupscount;
	$pages = ceil($total / $limit);
	$offset = ($page - 1)  * $limit;
	
	$query .= ' LIMIT :limit OFFSET :offset';

	// Prepare the paged query (if keyword isnt empty, it will be used)
	$groups = $pdo->prepare($query);
	$groups->bindParam(':uid', $userid, PDO::PARAM_INT);
	$groups->bindParam(':u', $keywordq, PDO::PARAM_STR);
	$groups->bindParam(':limit', $limit, PDO::PARAM_INT);
	$groups->bindParam(':offset', $offset, PDO::PARAM_INT);
	$groups->execute();
	
	//construct the json array
	$jsonData = array(
		"pageCount" => $pages,
		"pageResults" => (int)$groups->rowCount(),
		"keyword" => $keyword
	);
}
else
{
	// Prepare the paged query (if keyword isnt empty, it will be used)
	$groups = $pdo->prepare($query);
	$groups->bindParam(':uid', $userid, PDO::PARAM_INT);
	$groups->execute();
}

foreach($groups as $group)
{
	$id = $group['id'];
	$name = cleanOutput($group['name']);
	$description = cleanOutput($group['description']);
	$emblem = getAssetRender($group['emblem']);
	
	$jsonInfo = array(
		"id" => $id,
		"name" => $name,
		"description" => $description,
		"members" => groupMemberCount($id),
		"rank" => getRankName(getRank($userid,$id), $id),
		"emblem" => $emblem
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));