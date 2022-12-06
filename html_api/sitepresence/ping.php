<?php

/*
Fobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$success = false;
if ($GLOBALS['user']->UpdateLastSeen()) {
   $success = true;
}
die(json_encode(["success" => $success]));