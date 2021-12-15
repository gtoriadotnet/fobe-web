<?php

/*
Alphaland 2021 
The purpose of this is to upload SolidModels (Unions) from studio, since studio does not serialize the actual Union
*/

use Alphaland\Assets\Render;

$assetTypeName = $_GET['assetTypeName'];
$name = $_GET['name'];
$description = $_GET['description'];
$isPublic = $_GET['isPublic']; //isnt used in this script, but keeping anyways 
$genreTypeId = $_GET['genreTypeId']; //isnt used in this script, but keeping anyways 
$allowComments = $_GET['allowComments'];

//fuckload of checks but since people are SUS we need them
if ($assetTypeName && $name && $isPublic && $allowComments)
{
	if (!$description)
	{
		$description = "";
	}

	$offset = array_search($assetTypeName, assetTypeArray());

	if (!$offset == 0) //assetTypeName valid
	{
		if ($assetTypeName == "SolidModel") //restricting to solidmodel
		{
			$content = file_get_contents('php://input'); //content uploaded
			if ($content) //if content was uploaded
			{
				$decodedcontent = gzdecode($content); //decode the data
				if (strpos($decodedcontent, "<roblox!") !== false && strpos($decodedcontent, "PartOperationAsset") !== false) //very small check to see if its actually a valid roblox asset and a solidmodel (very hard to guess this is happening in the backend, so fine for now)
				{
					//generate new hash for the asset
					$assethash = genAssetHash(16);
					// ...
					
					//upload directory
					$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
					// ...
					
					//move uploaded data
					$success = file_put_contents($uploadDirectory . $assethash, $decodedcontent);
					// ...
					
					//if data was moved successfully
					if ($success != 0)
					{
						//add asset to db and return the id																	//(int)price	//(int)$isPublic
						//								                                                                		   v	       V						
						echo (CreateAsset($offset, 0, "TargetIdNotUsedHere", NULL, $name, $description, time(), time(), $user->id, 0, 0, 0, 0, 0, 1, 0, 0, (int)$allowComments, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, $assethash, NULL));
						// ...
					}
					// ...
				}
			}
		}
		else if ($assetTypeName == "Model")
		{
			if (strlen($name) < 50 && strlen($name) > 3 && strlen($description) < 1024)
			{
				if ($isPublic == "true")
				{
					$isPublic = true;
				}
				else 
				{
					$isPublic = false;
				}

				if ($allowComments == "true")
				{
					$allowComments = true;
				}
				else 
				{
					$allowComments = false;
				}

				$content = file_get_contents('php://input'); //content uploaded
				if ($content) //if content was uploaded
				{
					$decodedcontent = gzdecode($content); //decode the data
				
					//generate new hash for the asset
					$assethash = genAssetHash(16);
					// ...
						
					//upload directory
					$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
					// ...
						
					//move uploaded data
					$success = file_put_contents($uploadDirectory . $assethash, $decodedcontent);
					// ...
						
					//if data was moved successfully
					if ($success != 0)
					{
						//add asset to db and return the id																				//(int)$isPublic
						//		
						$newitem = CreateAsset(
							$offset, //AssetTypeId
							0, //IconImageAssetId
							NULL, //TargetId(not used atm)
							NULL, //ProductType(idk what to do with this atm)
							cleanInput($name), //Name
							cleanInput($description), //Description
							time(), //Created
							time(), //Updated
							$user->id, //CreatorId
							0, //PriceInAlphabux
							0, //Sales
							false, //isPersonalServer
							false, //IsNew
							$isPublic, //IsForSale
							$isPublic, //IsPublicDomain
							false, //IsLimited
							false, //IsLimitedUnique
							$allowComments, //IsCommentsEnabled
							true, //IsApproved
							false, //IsModerated
							0, //Remaining
							0, //MinimumMembershipLevel
							0, //ContentRatingTypeId
							0, //Favorited
							0, //Visited
							0, //MaxPlayers
							0, //UpVotes
							0, //DownVotes
							$assethash, //Hash
							NULL //ThumbHash
						);
						// ...
						if (!Render::RenderModel($newitem))
						{
							Render::RenderModel($newitem); //if first fail do it again
						}
						giveItem($user->id, $newitem);
					}
				}
			}
		}
	}
}