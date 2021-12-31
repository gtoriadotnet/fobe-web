<?php

/*
Alphaland 2021
*/

//vars

use Alphaland\Web\WebContextManager;

$thumbalive = false;
$gamealive = false;

//UTIL FUNCTIONS
function checkThumb($override)
{
	//thumbnailer check
	if (WebContextManager::HttpGetPing($GLOBALS['thumbnailArbiter'], 5000)) //thumb arbiter online
	{
		if (!$GLOBALS['thumbalive'] or $override) //to prevent flooding mysql calls
		{
			$GLOBALS['thumbalive'] = true;
			$set = $GLOBALS['pdo']->prepare("UPDATE websettings SET isThumbnailerAlive = 1");
			$set->execute();
		}
	}
	else //thumb arbiter offline
	{
		if ($GLOBALS['thumbalive'] or $override) //to prevent flooding mysql calls
		{
			$GLOBALS['thumbalive'] = false;
			$set = $GLOBALS['pdo']->prepare("UPDATE websettings SET isThumbnailerAlive = 0");
			$set->execute();
		}
	}
}

function checkGame($override)
{
	//gameserver check
	if (WebContextManager::HttpGetPing($GLOBALS['gamesArbiter'], 5000)) //gameserver arbiter online
	{
		if (!$GLOBALS['gamealive'] or $override) //to prevent flooding mysql calls
		{
			$GLOBALS['gamealive'] = true;
			$set = $GLOBALS['pdo']->prepare("UPDATE websettings SET IsGameServerAlive = 1");
			$set->execute();
		}
	}
	else //gameserver arbiter offline
	{
		if ($GLOBALS['gamealive'] or $override) //to prevent flooding mysql calls
		{
			$GLOBALS['gamealive'] = false;
			$set = $GLOBALS['pdo']->prepare("UPDATE websettings SET IsGameServerAlive = 0");
			$set->execute();
		}
	}
}

//first time running, pass true to force a check without SQL query limit restriction
checkGame(true);
checkThumb(true);

while (true) //EZ
{
	//we are in the loop now, run without override
	checkGame(false);
	checkThumb(false);
		
	usleep(10000); //if both requests timeout after 5 seconds, the max this script will halt is 20 seconds
}
