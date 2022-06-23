<?php

/*
	Finobe 2021
	We spawn this in a background process (only a few seconds) to prevent any blocking calls on the main php exec.
*/

$from = $argv[1];
$recipient = $argv[2];
$subject = base64_decode($argv[3]);
$body = base64_decode($argv[4]);
$altbody = base64_decode($argv[5]);

try 
{
	$GLOBALS['mail']->setFrom($from, $GLOBALS['siteName']);
	$GLOBALS['mail']->addAddress($recipient); 
	$GLOBALS['mail']->isHTML(true);
	$GLOBALS['mail']->Subject = $subject;
	$GLOBALS['mail']->Body    = $body;
	$GLOBALS['mail']->AltBody = $altbody;
	$GLOBALS['mail']->send();
} 
catch (Exception $e) 
{
	//analytics here
}