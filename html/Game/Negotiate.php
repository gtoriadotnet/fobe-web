<?php

/*
This is used on the client with a provided auth_ticket to authenticate the user
A cookie is set inside the client (IE)
TODO: Clean up
*/

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

if ($_SERVER['HTTP_USER_AGENT'] != "Roblox/WinInet") //user agent restricted
{
	die("Invalid request");
}

$token = (string)$_GET['suggest'];

$currenttoken = $GLOBALS['pdo']->prepare("SELECT * FROM user_auth_tickets WHERE token = :t");
$currenttoken->bindParam(":t", $token, PDO::PARAM_STR);
$currenttoken->execute();

if ($currenttoken->rowCount() > 0)
{
	$aInfo = $currenttoken->fetch(PDO::FETCH_OBJ);
	$whenGenerated = $aInfo->whenGenerated;
	$sessiontoken = $aInfo->session; //we arent creating a new session here anymore, we take the users current session token and set it in the client
	
	if(($whenGenerated + (300)) > time()) //under the 3 minute interval
	{
		//remove previous cookies
		setcookie("token", null, time(), "/", "idk16.xyz"); //delete non www. cookie
		setcookie("token", null, time(), "/", "www.idk16.xyz"); //delete www. cookie
		setcookie("token", null, time(), "/", ".idk16.xyz"); //delete (all token?) cookies
		// ...
		
		//set new cookie from auth ticket
		setcookie("token", $sessiontoken, time() + (86400 * 30), "/", ".idk16.xyz"); //30 day expiration on token for (hopefully) all fobe paths 
		// ...
		
		//setcookie("token", $sessiontoken, time() + (86400 * 30), "/", false, true); //30 day expiration on token
	}
	else
	{
		die("Auth expired");
	}
}
else
{
	die("Invalid auth");
}