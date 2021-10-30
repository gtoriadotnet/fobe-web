<?php

RCCHeaderEnvironment();

$placeid = (int)$_GET['PlaceID'];
$jobid = (string)$_GET['JobID'];

if (!$jobid || !$placeid)
{
	http_response_code(400);
}
else
{
	$server = $pdo->prepare("SELECT * FROM open_servers WHERE gameID = :g AND jobid = :j");
	$server->bindParam(":g", $placeid, PDO::PARAM_INT);
	$server->bindParam(":j", $jobid, PDO::PARAM_STR);
	$server->execute();
	
	if ($server->rowCount() > 0) //job exists with the parameters
	{
		$players = $pdo->prepare("SELECT * FROM game_presence WHERE placeid = :p AND jobid = :j AND (lastPing + 50) > UNIX_TIMESTAMP()");
		$players->bindParam(":p", $placeid, PDO::PARAM_INT);
		$players->bindParam(":j", $jobid, PDO::PARAM_STR);
		$players->execute();
		
		if ($players->rowCount() > 0) //atleast 1 player in job
		{
			soapRenewLease($GLOBALS['gamesArbiter'], $jobid, 90); //add 1.50 min to the job
		}
		
		$newping = $pdo->prepare("UPDATE open_servers SET lastPing = UNIX_TIMESTAMP() WHERE gameID = :g AND jobid = :j");
		$newping->bindParam(":g", $placeid, PDO::PARAM_INT);
		$newping->bindParam(":j", $jobid, PDO::PARAM_STR);
		$newping->execute();
	}
	else
	{
		http_response_code(400);
	}
}