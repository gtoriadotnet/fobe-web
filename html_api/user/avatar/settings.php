<?php


/*
Finobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$userid = $user->id;

$angleRight = (bool)userInfo($userid)->headshotAngleRight;
$angleLeft = (bool)userInfo($userid)->headshotAngleLeft;
$angleCenter = !$angleRight && !$angleLeft;

$headshotStyle = array(
	"angleCenter" => $angleCenter,
	"angleRight" => $angleRight,
	"angleLeft" => $angleLeft
);

die(json_encode($headshotStyle));