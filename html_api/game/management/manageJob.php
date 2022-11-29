<?php


/*
Finobe 2021 
*/

//headers

use Finobe\Grid\RccServiceHelper;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");

$placeid = $_GET['placeid'];
$jobid = $_GET['jobid'];
$shutdownserver = $_GET['shutdown'];

if (!$placeid || !$jobid || !isOwner($placeid))
{
	http_response_code(400);
}
else
{
	$q = $pdo->prepare("SELECT * FROM open_servers WHERE gameID = :pid AND jobid = :jid AND status = 1");
	$q->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$q->bindParam(":jid", $jobid, PDO::PARAM_STR);
	$q->execute();
	if($q->rowCount() > 0)
	{
		if ($shutdownserver) 
		{
			//doesnt have any return data, so we set message to true
			$jobClose = new RccServiceHelper($GLOBALS['gamesArbiter']);
			$jobClose->CloseJob($jobid);
			
			$q = $pdo->prepare("UPDATE open_servers SET status = 0 WHERE gameID = :pid AND jobid = :jid AND status = 1");
			$q->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$q->bindParam(":jid", $jobid, PDO::PARAM_STR);
			$q->execute();
			
			$message = true;
		}

		if ($message === true) {
			$message = "Success";
		}
		
		header('Content-Type: application/json');
		echo json_encode(array("alert" => $message));
	}
}