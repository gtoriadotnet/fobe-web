<?php

use Finobe\Assets\Render;
use Finobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

header("Cache-Control: no-cache, no-store");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

/*
	Finobe 2021
	This is responsible for handling datamodel auto saving
*/

$id = (int)$_GET['assetId'];

$iteminfo = getAssetInfo($id);

if($iteminfo !== FALSE) //asset id exists in finobe db
{
	if ($iteminfo->AssetTypeId == 9) //personal server place
	{
		$content = gzdecode(file_get_contents('php://input'));
		//TODO: implement a .RBXL parser
		if (strpos($content, "Workspace") !==false and strpos($content, "PhysicsService") !==false and strpos($content, "SoundService") !==false and strpos($content, "Lighting") !==false) 
		{
			if (isAssetApproved($id) and !isAssetModerated($id)) //if the asset is approved and not moderated
			{
				//generate new hash for the asset
				$assethash = genAssetHash(16);
				// ...
					
				//upload directory
				$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
				// ...
				
				$success = file_put_contents($uploadDirectory . $assethash, $content);

				if ($success)
				{
					//delete old hash
					unlink($uploadDirectory . $iteminfo->Hash);
					// ...
						
					//change asset to use new hash	
					$s = $pdo->prepare("UPDATE assets SET Hash = :hash WHERE id = :id");
					$s->bindParam(":hash", $assethash, PDO::PARAM_STR);
					$s->bindParam(":id", $iteminfo->id, PDO::PARAM_INT);
					$s->execute();
					// ...
					
					//epic
					if (isPlaceUsingRender($iteminfo->id))
					{
						Render::RenderPlace($iteminfo->id, true); //we pass true to fork from this session
					}
				}
			}
		}
	}
}