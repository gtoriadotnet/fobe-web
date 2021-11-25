<?php

/*
	Alphaland 2021
*/

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

if(!$user->isStaff()) {
    redirect("/MCP");
}

$bans = $GLOBALS['pdo']->prepare("SELECT * FROM user_bans WHERE valid = 1");
$bans->execute();
if ($bans->rowCount() == 0) {
	die(json_encode(["alert"=>"No bans found"]));
}

$jsonData = array();

foreach($bans as $ban) {
    $type = $ban['banType'];
    $banexpire = "";

    //bantypes to exp
    if ($type == 0) {
       $banexpire = "Warning";
    } else if ($type == 1) {
        $banexpire = date("m/d/Y", $ban['banExpiration']);
    } else if ($type == 2) {
        $banexpire = "Permanent";
    } else {
        $banexpire = "NULL";
    }
    
    $banData = array(
        "banType" => $type,
        "banReason" => cleanOutput($ban['banReason']),
        "bannedUser" => cleanOutput(getUsername($ban['uid'])),
        "whoBannedUser" => cleanOutput(getUsername($ban['whoBanned'])),
        "whenBanned" => date("m/d/Y", $ban['whenBanned']),
        "banExpiration" => $banexpire
    );
        
    array_push($jsonData, $banData);
}

die(json_encode($jsonData));