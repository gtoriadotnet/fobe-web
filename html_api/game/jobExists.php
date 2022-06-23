<?php


/*
Finobe 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.idk16.xyz");
header("access-control-allow-credentials: true");
header('Content-Type: application/json');

//get params
$placeid = $_GET['placeid'];
$jobid = $_GET['jobid'];

//vars
$result = false;

//query
$server = $pdo->prepare("SELECT * FROM open_servers WHERE status = 1 AND gameID = :gid AND jobid = :jid");
$server->bindParam(':gid', $placeid, PDO::PARAM_INT);
$server->bindParam(':jid', $jobid, PDO::PARAM_STR);
$server->execute();

if ($server->rowCount() > 0)
{
	$result = true;
}

echo json_encode(array(
	"result" => $result
));