<?php
header("Content-Type: application/json");

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(400));
}

if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
{
	if(isset($_POST["value"])&&isset($_GET["key"])&&isset($_GET["placeId"])&&isset($_GET["scope"])&&isset($_GET["type"])&&isset($_GET["target"]))
	{
		$values=[];
		$key = (string)$_GET["key"];
		$pid = (int)$_GET["placeId"];
		$scope = (string)$_GET["scope"];
		$type = (string)$_GET["type"];
		$target = (string)$_GET["target"];
		
		$query = "INSERT INTO persistence(`key`, placeId, type, scope, target, value) VALUES (:key,:pid,:type,:scope,:target,:val)";
		$queryChanged=false;
		
		$where = "WHERE placeId=:pid AND scope=:scope AND type=:type AND `key`=:key AND target=:target";
		
		$stmt = $pdo->prepare("SELECT * FROM persistence $where");
		$stmt->bindParam(':key', $key, PDO::PARAM_STR); 
		$stmt->bindParam(':pid', $pid, PDO::PARAM_INT); 
		$stmt->bindParam(':scope', $scope, PDO::PARAM_STR); 
		$stmt->bindParam(':type', $type, PDO::PARAM_STR); 
		$stmt->bindParam(':target', $target, PDO::PARAM_STR);
		$stmt->execute();
		if($stmt->rowCount()>0){
			$query = "UPDATE `persistence` SET `value`=:val $where";
		}
		
		$stmt = $pdo->prepare($query);
		$stmt->bindParam(':key', $key, PDO::PARAM_STR); 
		$stmt->bindParam(':pid', $pid, PDO::PARAM_INT); 
		$stmt->bindParam(':scope', $scope, PDO::PARAM_STR); 
		$stmt->bindParam(':type', $type, PDO::PARAM_STR); 
		$stmt->bindParam(':target', $target, PDO::PARAM_STR);
		$stmt->bindParam(':val', $_POST["value"], PDO::PARAM_STR);	
		$stmt->execute();
		$conn=null;
		
		$values = [array("Value"=>$_POST["value"],"Scope"=>$scope,"Key"=>$key,"Target"=>$target)];
		
		exit(json_encode(["data"=>$values], JSON_NUMERIC_CHECK));
	}
	exit(json_encode(["error"=>"An error occurred"]));
}
exit(json_encode(["error"=>"Failed to fetch client address."]));
?>