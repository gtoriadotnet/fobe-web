<?php

/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$twofactor = new Alphaland\Users\TwoFactor();
$userid = $user->id;

echo json_encode(array("success" => $twofactor::deleteUser2FA($userid)));