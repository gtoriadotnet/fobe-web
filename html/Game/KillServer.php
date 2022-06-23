<?php
use Finobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$jobID = (string)$_GET['jobid'];

$q = $pdo->prepare("SELECT * FROM open_servers WHERE jobid = :i AND status != 2");
$q->bindParam(":i", $jobID, PDO::PARAM_INT);
$q->execute();
if($q->rowCount() > 0) {
	$sInfo = $q->fetch(PDO::FETCH_OBJ);

	$u = $pdo->prepare("UPDATE open_servers SET status = 2, whenDied = UNIX_TIMESTAMP() WHERE id = :i");
	$u->bindParam(":i", $sInfo->id, PDO::PARAM_INT);
	$u->execute();
	
}