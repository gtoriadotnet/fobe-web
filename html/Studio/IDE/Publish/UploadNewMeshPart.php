<?php

/*
Alphaland 2021 
The purpose of this is to upload MeshParts from studio
*/;

$name = $_GET['name'];
$description = $_GET['description'];
$genreTypeId = $_GET['genreTypeId']; //isnt used in this script, but keeping anyways 
$allowComments = $_GET['allowComments'];

//fuckload of checks but since people are SUS we need them
if ($name && $description && $genreTypeId && $allowComments)
{
	if ($description == "MeshPart")
	{
		$content = file_get_contents('php://input'); //content uploaded
		if ($content) //if content was uploaded
		{
			$decodedcontent = gzdecode($content); //decode the data
			if (strpos($decodedcontent, "<roblox!") !== false) //very small check to see if it contains keyword for serialized data
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
					echo(
						CreateAsset(
						40, //AssetTypeId
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
						false, //IsForSale
						true, //IsPublicDomain
						false, //IsLimited
						false, //IsLimitedUnique
						(int)$allowComments, //IsCommentsEnabled
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
						)
					);
				}
			}
		}
	}
}