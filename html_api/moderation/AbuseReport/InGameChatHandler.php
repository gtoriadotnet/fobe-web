<?php

/*
    Alphaland 2021
    Abuse reports
*/

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

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