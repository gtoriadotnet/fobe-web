<?php

/*
Fobe 2021 
*/

//headers

use Fobe\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");

//get params
$userid = $_GET['userId'];
$headshot = $_GET['headshot'];
if (!$userid) {
	$userid = $user->id;
}

WebContextManager::Redirect(getPlayerRender($userid, $headshot)); //cachebuster