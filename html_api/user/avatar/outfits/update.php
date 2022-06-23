<?php


/*
Finobe 2021 
*/

//headers

use Finobe\Users\Outfit;

header("Access-Control-Allow-Origin: https://www.idk16.xyz");

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

		try {
			if (Outfit::UpdateOutfit($user->id, $outfitid, $name)) {
				$outfitchange = "Outfit Updated";
			}
		} catch (Exception $e) {
			$outfitchange = $e->getMessage();
		}
	}
	else if ($deleteoutfit)
	{
		$outfitid = $data->id;

		try {
			if (Outfit::DeleteOutfit($user->id, $outfitid)) {
				$outfitchange = "Outfit Deleted";
			}
		} catch (Exception $e) {
			$outfitchange = $e->getMessage();
		}
	}
	
	header('Content-Type: application/json');
	echo json_encode(array("alert" => $outfitchange));
}