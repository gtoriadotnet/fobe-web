<?php

/*
    Alphaland 2021
    Absue reports
*/

RCCHeaderEnvironment();

$xml = file_get_contents('php://input');

$validXML = true;
try {
    new SimpleXMLElement($xml);
} catch (Exception $e) {
    $validXML = false;
}

if ($validXML) {
    $report = $GLOBALS['pdo']->prepare("INSERT INTO `user_reports`(`report`, `whenReported`) VALUES(:report, UNIX_TIMESTAMP())");
    $report->bindParam(":report", $xml, PDO::PARAM_STR);
    $report->execute();
}