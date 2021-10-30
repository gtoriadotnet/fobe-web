<?php

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$xml = file_get_contents('compress.zlib://PlayerReport.txt');

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
    $reportreason = (string)trim($commentdata[1]);
    $reportdescription = (string)trim($commentdata[2]);

    $chats = $ParsedXML->xpath('//message');

    $jsonData = array(
		"ReporterUid" => $reporteruserid,
		"PlaceId" => $placeid, 
		"JobId" => $jobid,
		"AbuserId" => $abuserid,
		"Reason" => $reportreason,
		"Description" => $reportdescription
	);

	//die(json_encode($jsonData));

    foreach($chats as $chat) {
        $userid = (int)$chat['userID'];
        $userchat = (string)$chat;

        $chatData = array(
            "userid" => $userid,
            "chat" => $userchat
        );
        
        array_push($jsonData, $chatData);
    }

    die(json_encode($jsonData));
}