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

die(json_encode(["qr"=>$twofactor::getUser2FAQR($userid),"secret"=>$twofactor::getUser2FASecret($userid)]));