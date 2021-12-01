<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isAdmin())) {
	die('bababooey');
}

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'));
$jobid = $data->jobid;
$script = $data->script;

$output = "";
if (!isJobMarkedClosed($jobid))
{
	$output = soapExecuteEx($GLOBALS['gamesArbiter'], $jobid, "Execution From ACP", $script);
}

if (!$output->faultstring && $script) //logging
{
	$log = $GLOBALS['pdo']->prepare("INSERT INTO admin_job_execute_logs(userid, jobid, script, whenExecuted) VALUES (:uid, :jid, :script, UNIX_TIMESTAMP())");
	$log->bindParam(":uid", $user->id, PDO::PARAM_INT);
	$log->bindParam(":jid", $jobid, PDO::PARAM_STR);
	$log->bindParam(":script", $script, PDO::PARAM_STR);
	$log->execute();
}

echo json_encode(array("result" => $output->faultstring));