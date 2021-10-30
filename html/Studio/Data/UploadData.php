<?php

/*
Alphaland 2021
This is for uploading data from studio, this requires the user to have access to the asset.
*/

$id = (int)$_GET['id'];

$iteminfo = getAssetInfo($id);

if($iteminfo !== FALSE) //asset id exists in alphaland db
{
	if ($iteminfo->AssetTypeId == 9) //place
	{
		$content = gzdecode(file_get_contents('php://input'));
		//TODO: implement a .RBXL parser
		if (strpos($content, "Workspace") !==false and strpos($content, "PhysicsService") !==false and strpos($content, "SoundService") !==false and strpos($content, "Lighting") !==false) 
		{
			if (isAssetApproved($id) and !isAssetModerated($id)) //if the asset is approved and not moderated
			{
				if ($iteminfo->CreatorId == $user->id)
				{
					//generate new hash for the asset
					$assethash = genAssetHash(16);
					// ...
					
					//upload directory
					$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
					// ...
					
					//lock asset db
					$pdo->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
					// ...
					
					//delete old hash
					unlink($uploadDirectory . $iteminfo->Hash);
					// ...
					
					//change asset to use new hash	
					$s = $pdo->prepare("UPDATE assets SET Hash = :hash WHERE id = :id");
					$s->bindParam(":hash", $assethash, PDO::PARAM_STR);
					$s->bindParam(":id", $iteminfo->id, PDO::PARAM_INT);
					$s->execute();
					// ...
					
					//add new asset
					$success = file_put_contents($uploadDirectory . $assethash, $content);
					// ...
					
					//unlock asset db
					$pdo->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
					// ...
					
					if (isPlaceUsingRender($iteminfo->id))
					{
						RenderPlace($iteminfo->id);
					}
				}
				else
				{
					echo "Not Authorized to access this asset";
				}
			}
		}
	}
	else if ($iteminfo->AssetTypeId == 10) //model
	{
		$content = gzdecode(file_get_contents('php://input'));
		//TODO: implement a .RBXL parser
		if (isAssetApproved($id) and !isAssetModerated($id)) //if the asset is approved and not moderated
		{
			if ($iteminfo->CreatorId == $user->id)
			{
				//generate new hash for the asset
				$assethash = genAssetHash(16);
				// ...
					
				//upload directory
				$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
				// ...
					
				if (file_put_contents($uploadDirectory . $assethash, $content))
				{
					unlink($uploadDirectory . $iteminfo->Hash); //attempt to delete old hash, if doesnt work oh well

					//lock asset db
					$pdo->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive

					//change asset to use new hash	
					$s = $pdo->prepare("UPDATE assets SET Hash = :hash WHERE id = :id");
					$s->bindParam(":hash", $assethash, PDO::PARAM_STR);
					$s->bindParam(":id", $iteminfo->id, PDO::PARAM_INT);
					$s->execute();

					//unlock asset db
					$pdo->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff

					RenderModel($iteminfo->id);

				}
			}
			else
			{
				echo "Not Authorized to access this asset";
			}
		}
	}
}