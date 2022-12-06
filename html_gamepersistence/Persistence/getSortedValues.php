<?php
header("Content-Type: application/json");

use Fobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

if(isset($_GET["key"])&&isset($_GET["placeId"])&&isset($_GET["scope"])){
	$query = "SELECT * FROM persistence WHERE type=\"sorted\" AND placeid=:pid AND `key`=:key AND scope=:scope";
	$key = (string)$_GET["key"];;
	$pid = (int)$_GET["placeId"];;
	$scope = (string)$_GET["scope"];
	$limit = 0;
	$limitSet = isset($_GET["pageSize"]);
	if($limitSet){
		$query = $query . " LIMIT :limit";
		$limit = (int)$_GET["pageSize"];
	}
	$stmt = $pdo->prepare($query);
	$stmt->bindParam(':key', $key, PDO::PARAM_STR); 
	$stmt->bindParam(':pid', $pid, PDO::PARAM_INT); 
	$stmt->bindParam(':scope', $scope, PDO::PARAM_STR); 
	if($limitSet){
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT); 
	}
	$stmt->execute();
	$entries = [];
	$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach($result as &$data){
		array_push($entries,array("Target"=>$data["target"],"Value"=>$data["value"]));
	}
	$conn = null;
	exit(json_encode(["data"=>array("Entries"=>$entries)], JSON_NUMERIC_CHECK));
}
exit(json_encode(["error"=>"This driver can't. He just can't. Don't push him."]));
?>