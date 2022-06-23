<?php

use Finobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$jobid = (string)$_GET['jobId'];

$q = $pdo->prepare("SELECT * FROM open_servers WHERE jobid = :j AND status = 0");
$q->bindParam(":j", $jobid, PDO::PARAM_STR);
$q->execute();
if($q->rowCount() > 0) {
	$u = $pdo->prepare("UPDATE open_servers SET status = 1 WHERE jobid = :j");
	$u->bindParam(":j", $jobid, PDO::PARAM_STR);
	$u->execute();
}
die();