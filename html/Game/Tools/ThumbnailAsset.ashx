<?php

use Alphaland\Web\WebContextManager;

$fmt = $_GET['fmt'];
$wd = $_GET['wd'];
$ht = $_GET['ht'];
$aid = $_GET['aid'];

//grab the requested asset information
$check = $pdo->prepare("SELECT * FROM assets WHERE id = :i"); 
$check->bindParam(":i", $aid, PDO::PARAM_INT);
$check->execute();
// ...

//grab the default asset image
$defaultassetimage = 126; //default asset image is asset id 126
$defaultid = $pdo->prepare("SELECT * FROM assets WHERE id = :i"); 
$defaultid->bindParam(":i", $defaultassetimage, PDO::PARAM_INT);
$defaultid->execute();
$defaultid = $defaultid->fetch(PDO::FETCH_OBJ);
$defaultidhash = $defaultid->Hash;
// ...

//grab the moderated asset image
$moderatedassetimage = 193; 
$moderatedid = $pdo->prepare("SELECT * FROM assets WHERE id = :i"); 
$moderatedid->bindParam(":i", $moderatedassetimage, PDO::PARAM_INT);
$moderatedid->execute();
$moderatedid = $moderatedid->fetch(PDO::FETCH_OBJ);
$moderatedhash = $moderatedid->Hash;
// ...

//grab the pending asset image
$pendingassetimage = 194;
$pendingid = $pdo->prepare("SELECT * FROM assets WHERE id = :i"); 
$pendingid->bindParam(":i", $pendingassetimage, PDO::PARAM_INT);
$pendingid->execute();
$pendingid = $pendingid->fetch(PDO::FETCH_OBJ);
$pendinghash = $pendingid->Hash;
// ...

if ($check->rowCount() > 0) //asset exists on Alphaland
{
    $check = $check->fetch(PDO::FETCH_OBJ);

    if ($check->IsModerated == false && $check->IsApproved == true)
    {
        //assuming its none of these asset types, redirect to ROBLOX
        if ($check->AssetTypeId == 4) //handle mesh asset, return default image for now (TODO: RENDER THESE)
		{
			WebContextManager::Redirect("https://tcdn.alphaland.cc/" . $defaultidhash);
		}
		elseif ($check->AssetTypeId == 40) //handle MeshPart asset, return default image for now (TODO: RENDER THESE)
		{
			WebContextManager::Redirect("https://tcdn.alphaland.cc/" . $defaultidhash);
		}
		elseif ($check->AssetTypeId == 10) //handle model asset, return default image for now (TODO: RENDER THESE)
		{
			if (!empty($check->ThumbHash)) //if a render was ever performed
			{
				$thumbhash = $check->ThumbHash;

				WebContextManager::Redirect("https://trcdn.alphaland.cc/" . $thumbhash);
			}
			WebContextManager::Redirect("https://tcdn.alphaland.cc/" . $defaultidhash);
		}
		elseif ($check->AssetTypeId == 39) //handle SolidModel asset, return default image for now (TODO: RENDER THESE)
		{
			WebContextManager::Redirect("https://tcdn.alphaland.cc/" . $defaultidhash);
		}
    }
}
else
{
	//WebContextManager::Redirect(getRobloxAssetThumbnail($aid, $wd, $ht, $fmt)); //todo: fix this bullshit
}