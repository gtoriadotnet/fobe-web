<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
header('Content-Type: application/json');

$b = $pdo->prepare("SELECT * FROM user_signup_keys WHERE userGen = :userid");
$b->bindParam(":userid", $GLOBALS['user']->id, PDO::PARAM_INT);
$b->execute();

$jsonData = array();

foreach($b as $keyinfo)
{
	$whenGenerated = date("m/d/Y", $keyinfo['whenGenerated']);
	$signupkey = $keyinfo['signupkey'];
	
	$jsonInfo = array(
		"whenGenerated" => $whenGenerated,
		"key" => $signupkey,
	);
	
	array_push($jsonData, $jsonInfo);
}
// ...

die(json_encode($jsonData));