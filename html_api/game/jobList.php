<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$placeid = $_GET['placeid'];
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

//query
$query = "SELECT * FROM open_servers WHERE status = 1 AND gameID = :gid"; 

//count how many games without offset/limit
$serverscount = $pdo->prepare($query);
$serverscount->bindParam(':gid', $placeid, PDO::PARAM_INT);
$serverscount->execute();
$serverscount = $serverscount->rowCount();

//data for pages
$total = $serverscount;
$pages = ceil($total / $limit);
$offset = ($page - 1)  * $limit;

// Prepare the paged query (if keyword isnt empty, it will be used)
$servers = $pdo->prepare($query . ' LIMIT :limit OFFSET :offset');
$servers->bindParam(':gid', $placeid, PDO::PARAM_INT);
$servers->bindParam(':limit', $limit, PDO::PARAM_INT);
$servers->bindParam(':offset', $offset, PDO::PARAM_INT);
$servers->execute();

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
	"pageResults" => (int)$servers->rowCount()
);

foreach($servers as $server)
{
	$jobid = $server['jobid'];
	$playing = jobPlayerCount($server['gameID'], $jobid);
	$maxplayers = (int)getAssetInfo($server['gameID'])->MaxPlayers;
	$isowner = isOwner($server['gameID']);
	$whenStarted = date("h:ia", $server['whenStarted']);

	$playerslist = $pdo->prepare("SELECT * FROM game_presence WHERE placeid = :p AND jobid = :j AND (lastPing + 50) > UNIX_TIMESTAMP() ORDER BY whenJoined ASC");
	$playerslist->bindParam(':p', $placeid, PDO::PARAM_INT);
	$playerslist->bindParam(':j', $jobid, PDO::PARAM_STR);
	$playerslist->execute();

	$players = array();
	foreach($playerslist as $player)
	{	
		$userid = $player['uid'];
		$username = getUsername($userid);
		$thumbnail = getPlayerRender($userid);
	
		$playersInfo = array(
			"userid" => $userid,
			"username" => $username,
			"thumbnail" => "https://api.alphaland.cc/users/thumbnail?userId=".$userid."&headshot=true"
		);

		array_push($players, $playersInfo);
	}

	$serverInfo = array(
		"jobid" => $jobid,
		"playerscount" => $playing,
		"players" => $players,
		"maxPlayers" => $maxplayers,
		"isOwner" => $isowner,
		"whenStarted" => $whenStarted,
	);
	
	array_push($jsonData, $serverInfo);
}
// ...

die(json_encode($jsonData));