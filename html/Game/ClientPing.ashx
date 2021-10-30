<?php

if ($_SERVER['HTTP_USER_AGENT'] != $GLOBALS['clientUserAgent']) //user agent restricted
{
	die("Invalid request");
}

$userid = (int)$_GET['UserID'];
$placeid = (int)$_GET['PlaceID'];

$p = $pdo->prepare("SELECT * FROM game_presence WHERE uid = :u AND placeid = :p");
$p->bindParam(":u", $userid, PDO::PARAM_INT);
$p->bindParam(":p", $placeid, PDO::PARAM_INT);
$p->execute();

if ($p->rowCount() > 0)
{
	//presence valid, update the lastPing 
	$u = $pdo->prepare("UPDATE game_presence SET lastPing = UNIX_TIMESTAMP() WHERE uid = :u AND placeid = :p");
	$u->bindParam(":u", $userid, PDO::PARAM_INT);
	$u->bindParam(":p", $placeid, PDO::PARAM_INT);
	$u->execute();
}