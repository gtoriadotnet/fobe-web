<?php

/*
This is used on the client (if the client has the session token set) to request a server, join a server and server status messages
TODO: Clean up
*/

use Finobe\Assets\Asset;
use Finobe\Games\Game;
use Finobe\Grid\RccServiceHelper;

$requesttype = $_GET['request'];

$local = $_GET['local'];
$placeid = $_GET['placeid'];
$placeid2 = $_GET['placeId'];
if (!$placeid)
{
	$placeid = $placeid2;
}
$userid = $_GET['userid']; //for following
$isTeleport = $_GET['isTeleport'];

function constructJson($jobid, $status, $joinscripturl, $authenticationurl, $authenticationticket, $message)
{
	if (empty($message))
	{
		$message = null;
	}
	
	header('Content-Type: application/json');
	header("Cache-Control: no-cache, no-store");
	header("Pragma: no-cache");
	header("Expires: -1");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
				
	return json_encode(array(
		"jobId" => $jobid,
		"status" => $status,
		"joinScriptUrl" => $joinscripturl,
		"authenticationUrl" => $authenticationurl,
		"authenticationTicket" => $authenticationticket,
		"message" => $message
	), JSON_UNESCAPED_SLASHES);
}

if(!$requesttype || !$placeid || ($_SERVER['HTTP_USER_AGENT'] != $GLOBALS['clientUserAgent']))
{
	die(http_response_code(401));
}

function genToken($jobid) {
	$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_tokens WHERE uid = :u");
	$tokencheck->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$tokencheck->execute();
	if ($tokencheck->rowCount() > 0)
	{
		$tokenerase = $GLOBALS['pdo']->prepare("DELETE FROM game_launch_tokens WHERE uid = :u");
		$tokenerase->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
		$tokenerase->execute();
	}
	
	$t = genGameLaunchTokenHash(32);
	$n = $GLOBALS['pdo']->prepare("INSERT INTO game_launch_tokens(token,uid,jobid,whenCreated) VALUES(:t,:u,:s,UNIX_TIMESTAMP())");
	$n->bindParam(":t", $t, PDO::PARAM_INT);
	$n->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$n->bindParam(":s", $jobid, PDO::PARAM_INT);
	if($n->execute()) {
		return $t;
	}
}

function StartServer($gid) 
{
	$gameInfo = Asset::GetAssetInfo($gid);
	$jobuuid = Game::GenerateJobId(); //generate a UUID for the job
	$ip = $GLOBALS['gameMachine']; //IP address of the gameserver machine
	$port = Game::AllocatePort(); //generate an available port for the gameserver

	//add this server to the database
	$s = $GLOBALS['pdo']->prepare("INSERT INTO open_servers(jobid,gameID,ip,port,whenStarted,lastPing) VALUES(:j,:g,:ip,:port,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
	$s->bindParam(":j", $jobuuid, PDO::PARAM_STR);
	$s->bindParam(":g", $gid, PDO::PARAM_INT);
	$s->bindParam(":ip", $ip, PDO::PARAM_STR);
	$s->bindParam(":port", $port, PDO::PARAM_INT);
	$s->execute();
	
	//launch the server
	$script = file_get_contents($GLOBALS['gameserverscript']);

	$gameSpawnResult = new RccServiceHelper($GLOBALS['gamesArbiter']);
	$gameSpawnResult->OpenJobEx(
		$gameSpawnResult->ConstructGenericJob($jobuuid, 60, 0, 0, "Start Server ".$gid, $script, array(
			$gid, //placeid
			$port, //gameserver port
			$GLOBALS['domain'], //domain
			$gameInfo->CreatorId, //place creatorid
			(bool)$gameInfo->isPersonalServer //ispersonalserver
		))
	);
	
	return $jobuuid; //return the new job UUID
}

if ($requesttype == "RequestGame") //start new server or join existing one
{
	$check = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $placeid, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0)
	{
		$gInfo = $check->fetch(PDO::FETCH_OBJ);
		
		$assettype = $gInfo->AssetTypeId;
		
		if ($assettype == 9) //asset is game
		{
			//safe ID
			$gameID = $gInfo->id;
	
			Game::CloseDeadJobs($gameID);

			if (Game::UserAccess($gameID, $user->id))
			{
				$ispbs = (bool)$gInfo->isPersonalServer;
				
				//check for open servers
				$query = "SELECT * FROM open_servers WHERE gameID = :i AND (status = 0 OR status = 1) ORDER BY status DESC LIMIT 1";
				if(!$ispbs) {
					$query = "SELECT open_servers.*
							FROM open_servers
							LEFT OUTER JOIN game_presence
							ON open_servers.jobid = game_presence.jobid
							WHERE open_servers.gameID = :i
							AND (open_servers.status = 0 or open_servers.status = 1)
							AND (SELECT COUNT(*) FROM game_presence WHERE game_presence.jobid = open_servers.jobid AND (game_presence.lastPing + 50) > UNIX_TIMESTAMP()) < :mp
							ORDER BY (SELECT COUNT(*) FROM game_presence WHERE game_presence.jobid = open_servers.jobid AND (game_presence.lastPing + 50) > UNIX_TIMESTAMP())
							LIMIT 1;";
				}
				
				$servers = $pdo->prepare($query);
				$servers->bindParam(":i", $gameID, PDO::PARAM_INT);
				if(!$ispbs) {
					$servers->bindParam(":mp", $gInfo->MaxPlayers, PDO::PARAM_INT);
				}
				$servers->execute();
				if($servers->rowCount() > 0) //server already available
				{
					$sInfo = $servers->fetch(PDO::FETCH_OBJ);
					if($sInfo->status == 0) //game is opening, send retry signal
					{
						echo constructJson($sInfo->jobid."", 0, "", "", "", ""); //retry signal
					}
					elseif($sInfo->status == 1) //game is open, check if its joinable (player count, queue, etc)
					{
						Game::AddPlayerToQueue($gameID, $sInfo->jobid, $user->id); //add player to queue (if they are in it, this updates ping)
						if (Game::IsNextInQueue($gameID, $sInfo->jobid, $user->id)) //player next in queue
						{
							if (Game::JobPlayerCount($gameID, $sInfo->jobid) >= $gInfo->MaxPlayers)
							{
								echo constructJson($sInfo->jobid."", 6, "", "", "", ""); //return job full
							}
							else
							{
								$newticket = genToken($sInfo->jobid);
								echo constructJson($sInfo->jobid."", 2, "https://idk16.xyz/Game/Join?ticket=" .$newticket, "", "", "");
							}
						}
						else
						{
							echo constructJson($sInfo->jobid."", 6, "", "", "", ""); //return job full
						}
					}
				}
				else //no available servers
				{
					$sQ = $pdo->prepare($query);
					$sQ->bindParam(":i", $gameID, PDO::PARAM_INT);
					if(!$ispbs) {
						$servers->bindParam(":mp", $gInfo->MaxPlayers, PDO::PARAM_INT);
					}
					$sQ->execute();
							
					if($sQ->rowCount() == 0) //check one more time if a server spawned
					{
						$newjob = StartServer($gameID);

						echo constructJson($newjob."", 0, "", "", "", ""); //retry signal
					}
				}
			}
		}
	}
}
else if ($requesttype == "RequestFollowUser") //follow user
{
	if ($userid)
	{
		$check = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
		$check->bindParam(":i", $placeid, PDO::PARAM_INT);
		$check->execute();
		if($check->rowCount() > 0) //asset exists
		{
			$gInfo = $check->fetch(PDO::FETCH_OBJ);
		
			$assettype = $gInfo->AssetTypeId;
			
			if ($assettype == 9) //asset is a game
			{
				Game::CloseDeadJobs($placeid);
				
				$playersgame = $pdo->prepare("SELECT * FROM game_presence WHERE uid = :u AND placeid = :p");
				$playersgame->bindParam(":u", $userid, PDO::PARAM_INT);
				$playersgame->bindParam(":p", $placeid, PDO::PARAM_INT);
				$playersgame->execute();
				
				if ($playersgame->rowCount() > 0) //player is in a job, check if full
				{
					$playersgamejobid = $playersgame->fetch(PDO::FETCH_OBJ)->jobid;
				
					$mcheck = $pdo->prepare("SELECT COUNT(*) FROM game_presence WHERE jobid = :j AND (lastPing + 50) > UNIX_TIMESTAMP()");
					$mcheck->bindParam(":j", $playersgamejobid, PDO::PARAM_STR);
					$mcheck->execute();
					
					if($mcheck->fetchColumn(0) >= $gInfo->MaxPlayers) //players job is full
					{
						echo constructJson($mcheck->fetch(PDO::FETCH_OBJ)->jobid."", 6, "", "", "", ""); //return job full
					}
					else //job isnt full, join it
					{
						$newticket = genToken($playersgamejobid);
						echo constructJson($playersgamejobid."", 2, "https://idk16.xyz/Game/Join?ticket=" .$newticket, "", "", "");
					}
				}
				else //user left game
				{
					echo constructJson("", 10, "", "", "", ""); //user left job signal
				}
			}
		}
	}
}