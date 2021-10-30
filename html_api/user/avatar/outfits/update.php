<?php


/*
Alphaland 2021 
*/

//headers
header("Access-Control-Allow-Origin: https://www.alphaland.cc");

header("access-control-allow-credentials: true");

$updateoutfit = (bool)$_GET['update'];
$deleteoutfit = (bool)$_GET['delete'];
$data = json_decode(file_get_contents('php://input'));

if (!$data)
{
	http_response_code(400);
}
else
{
	$outfitchange = "";
	if ($updateoutfit)
	{
		$outfitid = $data->id;
		$name = $data->name;

		$outfitchange = updateOutfit($user->id, $outfitid, $name);
		if ($outfitchange === true) {
			$outfitchange = "Outfit Updated";
		}
	}
	else if ($deleteoutfit)
	{
		$outfitid = $data->id;

		$outfitchange = deleteOutfit($user->id, $outfitid);
		if ($outfitchange === true) {
			$outfitchange = "Outfit Deleted";
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $outfitchange));
}