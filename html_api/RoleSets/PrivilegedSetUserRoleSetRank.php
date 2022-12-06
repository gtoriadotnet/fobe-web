<?php

use Fobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

/*
Fobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$placeid = (int)$_GET['placeId'];
$userid = (int)$_GET['userId'];
$rank = (int)$_GET['newRank'];

updateBuildServerRank($placeid, $userid, $rank);