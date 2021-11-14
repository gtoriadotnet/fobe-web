<?php

/*
	Alphaland 2021
	Report Data
*/

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$id = (int)$_GET['id'];

if(!$user->isStaff() || !$id) {
    redirect("/MCP");
}

$xml = "";

$report = $GLOBALS['pdo']->prepare("SELECT * FROM user_reports WHERE id = :id");
$report->bindParam(":id", $id, PDO::PARAM_INT);
$report->execute();
if ($report->rowCount() > 0) {
	$xml = $report->fetch(PDO::FETCH_OBJ)->report;
} else {
	die(json_encode(["alert"=>"Report not found"]));
}

$validXML = true;
try {
    $ParsedXML = new SimpleXMLElement($xml);
} catch (Exception $e) {
    $validXML = false;
}

if ($validXML) {
    $reporteruserid = (int)$ParsedXML->attributes()->userID;
    $placeid = (int)$ParsedXML->attributes()->placeID;
    $jobid = (string)$ParsedXML->attributes()->gameJobID;

    $commentdata = explode(";", $ParsedXML->comment);

    $abuserid = (int)filter_var($commentdata[0], FILTER_SANITIZE_NUMBER_INT);
    $reportreason = (string)cleanOutputNoFilter(trim($commentdata[1]));
    $reportdescription = (string)cleanOutputNoFilter($commentdata[2]);

    $chats = $ParsedXML->xpath('//message');

    $jsonData = array(
		"ReporterUid" => $reporteruserid,
		"PlaceId" => $placeid, 
		"JobId" => $jobid,
		"AbuserUid" => $abuserid,
		"Reason" => $reportreason,
		"Description" => $reportdescription
	);

    foreach($chats as $chat) {
        $userid = (int)$chat['userID'];
		$username = (string)getUsername($userid);
        $userchat = (string)cleanOutputNoFilter($chat);

        $chatData = array(
			"username" => $username,
            "userid" => $userid,
            "chat" => $userchat
        );
        
        array_push($jsonData, $chatData);
    }

    die(json_encode($jsonData));
}
die(json_encode(["alert"=>"Error Occurred"]));