<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

header("Access-Control-Allow-Origin: https://crackpot.alphaland.cc");
header("access-control-allow-credentials: true");

if(!($user->isAdmin())) {
	die('bababooey');
}

$salt = "Rg2g5QZqjcQK"; //current salt for game security version
$securityversion = json_decode(file_get_contents('php://input'))->version;

if ($securityversion)
{	
	echo json_encode(array("result" => str_rot13(sha1($securityversion . $salt))));
}