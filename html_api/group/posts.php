<?php

/*
Fobe 2021 
*/


//headers

use Fobe\Groups\Group;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$groupid = (int)$_GET['id'];
$page = $_GET['page'];
$limit = $_GET['limit'];

//people without permission cant go snooping from the api
if (!Group::WallViewPermission($user->id, $groupid))
{
	http_response_code(400);
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

//query
$query = "SELECT * FROM group_posts WHERE groupid = :gid ORDER BY postDate DESC"; 

//count how many games without offset/limit
$postscount = $pdo->prepare($query);
$postscount->bindParam(':gid', $groupid, PDO::PARAM_STR);
$postscount->execute();
$postscount = $postscount->rowCount();

//data for pages
$total = $postscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query
$posts = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$posts->bindParam(':gid', $groupid, PDO::PARAM_INT);
$posts->bindParam(':limit', $limit, PDO::PARAM_INT);
$posts->bindParam(':offset', $offset, PDO::PARAM_INT);
$posts->execute();

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
	"pageResults" => (int)$posts->rowCount()
);

foreach($posts as $post)
{
	$userid = $post['userid']; //id of the game
	$groupid = (int)$groupid;
	$postid = $post['id'];
	$posts = cleanOutput($post['post']); //creator of the game username
	$postdate = date("m/d/Y", $post['postdate']); //players in the game	
	$thumbnail = getPlayerRender($userid);
	
	$postsInfo = array(
		"groupid" => $groupid,
		"postid" => $postid,
		"username" => getUsername($userid),
		"userid" => $userid,
		"post" => $posts,
		"postdate" => $postdate,
		"thumbnail" => "https://api.idk16.xyz/users/thumbnail?userId=".$userid."&headshot=true"
	);
	
	array_push($jsonData, $postsInfo);
}
// ...

die(json_encode($jsonData));