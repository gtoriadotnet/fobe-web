<?php

/*
	Alphaland 2021
*/

use Alphaland\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

if(!$user->isStaff()) {
    WebContextManager::Redirect("/");
}

$report = $GLOBALS['pdo']->prepare("SELECT * FROM user_reports WHERE closed = 0 ORDER BY whenReported ASC");
$report->execute();
if ($report->rowCount() == 0) {
	die(json_encode(["alert"=>"No reports found"]));
}

$jsonData = array();

foreach($report as $report) {
    $chatData = array(
        "id" => $report['id'],
        "reported" => date("m/d/Y", $report['whenReported'])
    );
        
    array_push($jsonData, $chatData);
}

die(json_encode($jsonData));