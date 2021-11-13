<?php


/*
Alphaland 2021
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userid = $user->id;

die(json_encode(["qr"=>getUser2FAQR($userid),"secret"=>getUser2FASecret($userid)]));