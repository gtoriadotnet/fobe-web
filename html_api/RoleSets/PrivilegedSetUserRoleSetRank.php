<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$placeid = (int)$_GET['placeId'];
$userid = (int)$_GET['userId'];
$rank = (int)$_GET['newRank'];

updateBuildServerRank($placeid, $userid, $rank);