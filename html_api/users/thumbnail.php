<?php

/*
Alphaland 2021 
*/

//headers

use Alphaland\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

//get params
$userid = $_GET['userId'];
$headshot = $_GET['headshot'];
if (!$userid) {
	$userid = $user->id;
}

WebContextManager::Redirect(getPlayerRender($userid, $headshot)); //cachebuster