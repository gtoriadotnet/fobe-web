<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");
header('Content-Type: application/json');

$bc = $pdo->prepare("SELECT * FROM body_colours WHERE uid = :u");
$bc->bindParam(":u", $user->id, PDO::PARAM_INT);
$bc->execute();
if($bc->rowCount() > 0) 
{
	$bc = $bc->fetch(PDO::FETCH_OBJ);

	$bodyInfo = array(
		"Head" => getBC($bc->h),
		"Torso" => getBC($bc->t),
		"RightArm" => getBC($bc->ra),
		"RightLeg" => getBC($bc->rl),
		"LeftArm" => getBC($bc->la),
		"LeftLeg" => getBC($bc->ll)
	);
	
	die(json_encode($bodyInfo));
} 
else
{
	http_response_code(400);
}
