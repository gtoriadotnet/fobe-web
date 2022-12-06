<?php


/*
Fobe 2021
*/

//headers

use Fobe\Users\TwoFactor;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$userid = $user->id;

die(json_encode([
    "qr" => TwoFactor::getUser2FAQR($userid),
    "secret" => TwoFactor::GetUser2FASecret($userid)
]));