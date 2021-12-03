<?php


/*
Alphaland 2021 
*/

//headers

use Alphaland\Users\ReferralProgram;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");
header('Content-Type: application/json');

$key = ReferralProgram::GenerateUserKey($user->id);
$alert = "";
if ($key == "Error occurred" || $key == "Maximum keys generated, check back in a week." || $key == "Maximum of two active keys.") //ghetto as well
{
	$alert = $key;
	$key = "";
}

$generatedKey = array(
	"alert" => $alert,
	"key" => $key
);

die(json_encode($generatedKey));