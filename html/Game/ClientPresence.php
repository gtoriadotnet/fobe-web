<?php

//the design choice here was to tie in clientpresence with recently played and visits and make it fully server-sided besides the client pings

use Alphaland\Games\Game;
use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$action = (string)$_GET['action'];
$userid = (int)$_GET['UserID'];
$placeid = (int)$_GET['PlaceID'];
$jobid = (string)$_GET['JobID'];
$isteleport = (bool)$_GET['IsTeleport'];

function BadRequest()
{
	die(http_response_code(401));
}

if (!$action || !$userid || !$placeid)
{
	BadRequest();
}

if (!userExists($userid))
{
	BadRequest();
}

$p = $pdo->prepare("SELECT * FROM game_launch_tokens WHERE uid = :u AND jobid = :jid");
$p->bindParam(":u", $userid, PDO::PARAM_INT);
$p->bindParam(":jid", $jobid, PDO::PARAM_INT);
$p->execute();
if ($p->rowCount() == 0) //no hits, so not valid user
{
	BadRequest();
}


if ($action == "disconnect")
{
	$u = $pdo->prepare("DELETE FROM game_presence WHERE uid = :i");
	$u->bindParam(":i", $userid, PDO::PARAM_INT);
	$u->execute();
}
else if ($action == "connect")
{
	//check if user already has a presence, delete if exists
	$p = $pdo->prepare("SELECT * FROM game_presence WHERE uid = :u");
	$p->bindParam(":u", $userid, PDO::PARAM_INT);
	$p->execute();
	
	if ($p->rowCount() > 0)
	{
		$u = $pdo->prepare("DELETE FROM game_presence WHERE uid = :i");
		$u->bindParam(":i", $userid, PDO::PARAM_INT);
		$u->execute();
	}
	// ... 
	
	//create player presence (pass in the current servers job id for following functionality)
	$g = $pdo->prepare("INSERT INTO game_presence(uid,placeid,jobid,whenJoined,lastPing) VALUES(:u,:p,:j,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
	$g->bindParam(":u", $userid, PDO::PARAM_INT);
	$g->bindParam(":p", $placeid, PDO::PARAM_INT);
	$g->bindParam(":j", $jobid, PDO::PARAM_STR);
	$g->execute();
	// ...

	//remove them from queue (once presence is created above, placelauncher will detect playercount correctly. should be very rare for two people to get in a single slot)
	Game::RemovePlayerFromQueue($userid);
	
	//update or create player recently played (TODO: restrict to 4 database entries to save space)
	$checkforrecent = $pdo->prepare("SELECT * FROM game_recents WHERE uid = :i AND gid = :g");
	$checkforrecent->bindParam(":i", $userid, PDO::PARAM_INT);
	$checkforrecent->bindParam(":g", $placeid, PDO::PARAM_INT);
	$checkforrecent->execute();
					
	if ($checkforrecent->rowCount() > 0)
	{
		//update recently played
		$setgamerecent = $pdo->prepare("UPDATE game_recents SET whenPlayed = UNIX_TIMESTAMP() WHERE uid = :u AND gid = :g");
		$setgamerecent->bindParam(":u", $userid, PDO::PARAM_INT);
		$setgamerecent->bindParam(":g", $placeid, PDO::PARAM_INT);
		$setgamerecent->execute();
		//...
	}
	else 
	{
		//create recently played
		$setgamerecent = $pdo->prepare("INSERT INTO game_recents(uid,gid,whenPlayed) VALUES(:u,:g,UNIX_TIMESTAMP())");
		$setgamerecent->bindParam(":u", $userid, PDO::PARAM_INT);
		$setgamerecent->bindParam(":g", $placeid, PDO::PARAM_INT);
		$setgamerecent->execute();
		//...
	}
	// ...
	
	//place visit
	$new_visit = false;
	
	$visit = $pdo->prepare("SELECT * FROM game_unique_visit WHERE uid = :i AND gid = :g");
	$visit->bindParam(":i", $userid, PDO::PARAM_INT);
	$visit->bindParam(":g", $placeid, PDO::PARAM_INT);
	$visit->execute();
					
	if ($visit->rowCount() > 0) //we got a hit
	{
		$info = $visit->fetch(PDO::FETCH_OBJ);
		if(($info->visited + (86400 * 1)) > time()) //one day
		{
			return;
		}
		else //no visit to the game today
		{
			$setuniquevisit = $pdo->prepare("UPDATE game_unique_visit SET visited = :t WHERE uid = :u AND gid = :g");
			$setuniquevisit->bindParam(":u", $userid, PDO::PARAM_INT);
			$setuniquevisit->bindParam(":g", $placeid, PDO::PARAM_INT);
			$setuniquevisit->bindParam(":t", time(), PDO::PARAM_INT);
			$setuniquevisit->execute();
							
			$new_visit = true;
		}
	}
	else
	{
		$setuniquevisit = $pdo->prepare("INSERT INTO game_unique_visit(uid,gid,visited) VALUES(:u,:g,:t)");
		$setuniquevisit->bindParam(":u", $userid, PDO::PARAM_INT);
		$setuniquevisit->bindParam(":g", $placeid, PDO::PARAM_INT);
		$setuniquevisit->bindParam(":t", time(), PDO::PARAM_INT);
		$setuniquevisit->execute();
							
		$new_visit = true;
	}
			
	$creatorid = getAssetInfo($placeid)->CreatorId;
	if ($new_visit && $creatorid != $userid)
	{
		$setgamevisit = $pdo->prepare("UPDATE assets SET Visited = (Visited + 1) WHERE id = :g");
		$setgamevisit->bindParam(":g", $placeid, PDO::PARAM_INT);
		$setgamevisit->execute();

		giveCurrency(1, $creatorid);
	}
	// ...
}
else
{
	BadRequest(); //something RCC will show if the request is failing for some reason
}