<?php

/*
Fobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$twofactor = new Fobe\Users\TwoFactor();
$userid = $user->id;

echo json_encode(array("success" => $twofactor::deleteUser2FA($userid)));