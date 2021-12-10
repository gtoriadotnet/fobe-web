<?php


/*
Alphaland 2021 
*/

//headers

use Alphaland\Grid\RccServiceHelper;

header("Access-Control-Allow-Origin: https://www.alphaland.cc");
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
			$message = true;
		}

		if ($message === true) {
			$message = "Success";
		}
		
		header('Content-Type: application/json');
		echo json_encode(array("alert" => $message));
	}
}