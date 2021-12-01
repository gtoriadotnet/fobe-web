<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isAdmin())) {
	die('bababooey');
}

//headers
header("Access-Control-Allow-Origin: https://crackpot.alphaland.cc");
header("access-control-allow-credentials: true");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
header('Content-Type: application/json');

$b = $pdo->prepare("SELECT * FROM open_servers WHERE status = 1 ORDER BY gameID ASC");
$b->execute();

$jsonData = array();

foreach($b as $jobInfo)
{
	$jobid = $jobInfo['jobid'];
	$placeid = $jobInfo['gameID'];
	checkForDeadJobs($placeid);
	
	$jsonInfo = array(
		"JobID" => $jobid,
		"PlaceID" => $placeid,
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));