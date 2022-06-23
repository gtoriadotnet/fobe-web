<?php

/*
	Finobe 2021
	Closes an active report
*/

use Finobe\Web\WebContextManager;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$id = (int)$_GET['id'];

if(!$user->IsStaff() || !$id) {
    WebContextManager::Redirect("/");
}

$report = $GLOBALS['pdo']->prepare("UPDATE user_reports SET `closed` = 1 WHERE `id` = :id AND `closed` = 0");
$report->bindParam(":id", $id, PDO::PARAM_INT);
$report->execute();
if ($report->rowCount() > 0) {
	die(json_encode(["alert"=>"Closed Report"]));
} else {
	die(json_encode(["alert"=>"Invalid Report"]));
}