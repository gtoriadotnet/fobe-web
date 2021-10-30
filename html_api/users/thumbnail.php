<?php

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");

//get params
$userid = $_GET['userId'];
$headshot = $_GET['headshot'];
if (!$userid) {
	$userid = $user->id;
}

redirect(getPlayerRender($userid, $headshot)); //cachebuster