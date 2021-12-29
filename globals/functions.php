<?php

/*
	Alphaland 2021
	A bunch of global functions used sitewide
	TODO: clean up a lot of legacy code
*/

use Alphaland\Assets\Render;
use Alphaland\Games\Game;
use Alphaland\Moderation\Filter;
use Alphaland\Users\Render as UsersRender;
use Alphaland\Users\User;
use Alphaland\Web\WebContextManager;

//safe generation utilities

function genHash($len)
{
	return bin2hex(openssl_random_pseudo_bytes($len));
}

function genTicketHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM user_auth_tickets WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genVerifcationEmailHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genResetPasswordHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genSignupKeyHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM signup_keys WHERE signupkey = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genAssetHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE Hash = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		}
		else {
			$alloc = false;
		}
	}
	return $hash;
}

function genGameLaunchTokenHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc)
	{
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_tokens WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

//gen uuid
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

//

//auth ticket utilities

function genTicket() 
{
	$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM user_auth_tickets WHERE uid = :u");
	$tokencheck->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$tokencheck->execute();
	if ($tokencheck->rowCount() > 0) {
		$tokenerase = $GLOBALS['pdo']->prepare("DELETE FROM user_auth_tickets WHERE uid = :u");
		$tokenerase->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
		$tokenerase->execute();
	}
	
	$t = genTicketHash(64); //128 characters long (value + value)
	$n = $GLOBALS['pdo']->prepare("INSERT INTO user_auth_tickets(token,session,uid,whenGenerated) VALUES(:t,:s,:u,UNIX_TIMESTAMP())");
	$n->bindParam(":t", $t, PDO::PARAM_STR);
	$n->bindParam(":s", $_COOKIE['token'], PDO::PARAM_STR);
	$n->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	if($n->execute()) {
		return $t;
	}
	return false;
}

//end auth ticket utilities

//TODO: Render Queue?

function logChatMessage($userid, $text, $trippedfilter) //privacy concern?
{
	if (userInfo($userid)->rank != 2) //dont log admins chats
	{
		$trippedfilter = boolval($trippedfilter);
		$p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
		$p->bindParam(":i", $userid, PDO::PARAM_INT);
		$p->execute();
		$placeid = $p->fetch(PDO::FETCH_OBJ)->placeid;		

		$logmessage = $GLOBALS['pdo']->prepare("INSERT INTO chat_logs(message, gameAssetId, whoSent, whenSent, trippedFilter) VALUES (:message, :gameAssetId, :whoSent, UNIX_TIMESTAMP(), :trippedFilter)");
		$logmessage->bindParam(":message", $text, PDO::PARAM_STR);
		$logmessage->bindParam(":gameAssetId", $placeid, PDO::PARAM_INT);
		$logmessage->bindParam(":whoSent", $userid, PDO::PARAM_INT);
		$logmessage->bindParam(":trippedFilter", $trippedfilter, PDO::PARAM_INT);
		$logmessage->execute();
	}
}

//personal build servers 

function updateBuildServerRank($placeid, $userid, $rank)
{
	if ($userid != getAssetInfo($placeid)->CreatorId && getAssetInfo($placeid)->isPersonalServer == 1)
	{
		if ($rank == 240 || $rank == 128 || $rank == 0) //PBS RANKS ADMIN, MEMBER AND BANNED
		{
			$exists = $GLOBALS['pdo']->prepare("SELECT * FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
			$exists->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$exists->bindParam(":uid", $userid, PDO::PARAM_INT);
			$exists->execute();
			if ($exists->rowCount() > 0) //we got a rank already, update
			{
				$setrank = $GLOBALS['pdo']->prepare("UPDATE personal_build_ranks SET rank = :rank WHERE placeid = :pid AND userid = :uid");
				$setrank->bindParam(":rank", $rank, PDO::PARAM_INT);
				$setrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
				$setrank->bindParam(":uid", $userid, PDO::PARAM_INT);
				$setrank->execute();
				return true;
			}
			else //no rank, create new one
			{
				$newrank = $GLOBALS['pdo']->prepare("INSERT INTO personal_build_ranks(placeid, userid, rank, whenRanked) VALUES (:pid, :uid, :rank, UNIX_TIMESTAMP())");
				$newrank->bindParam(":rank", $rank, PDO::PARAM_INT);
				$newrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
				$newrank->bindParam(":uid", $userid, PDO::PARAM_INT);
				$newrank->execute();
				return true;
			}
		}
		else if ($rank == 10) //PBS RANK VISITOR
		{
			$delrank = $GLOBALS['pdo']->prepare("DELETE FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
			$delrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$delrank->bindParam(":uid", $userid, PDO::PARAM_INT);
			$delrank->execute();
			return true;
		}
	}
	return "Error occurred";
}

function getBuildServerRank($placeid, $userid)
{
	if ($userid == getAssetInfo($placeid)->CreatorId)
	{
		return 255;
	}
	else
	{
		$rank = $GLOBALS['pdo']->prepare("SELECT * FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
		$rank->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$rank->bindParam(":uid", $userid, PDO::PARAM_INT);
		$rank->execute();
		if ($rank->rowCount() > 0)
		{
			return $rank->fetch(PDO::FETCH_OBJ)->rank;
		}
	}
	return 10; //no rank. consider them Visitor rank
}

function removePBSUser($placeid, $userid)
{
	$remove = $GLOBALS['pdo']->prepare("DELETE FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
	$remove->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$remove->bindParam(":uid", $userid, PDO::PARAM_INT);
	$remove->execute();
	if ($remove->rowCount() > 0)
	{
		return true;
	}
	return "Error occurred";
}

function updatePBSGameSettings($placeid, $name, $description, $commentsenabled, $whitelistenabled, $maxplayers)
{
	if (isOwner($placeid) && getAssetInfo($placeid)->isPersonalServer == 1)
	{
		$name = cleanInput($name);
		$description = cleanInput($description);
		$comments = boolval($commentsenabled);
		$whitelistenabled = boolval($whitelistenabled);
		$players = (int)$maxplayers;

		if (getAssetInfo($placeid)->Name != $name) //dont run if name hasnt changed
		{
			if (strlen($name) > 50)
			{
				return "Name too long";
			}
			else if (strlen($name) < 3)
			{
				return "Name too short";
			}
		}
		
		if (getAssetInfo($placeid)->Description != $description) //dont run if description hasnt changed
		{
			if (strlen($description) > 1024)
			{
				return "Description too long";
			}
			else if (strlen($description) < 3)
			{
				return "Description too short";
			}
		}

		if (getAssetInfo($placeid)->IsCommentsEnabled != $comments) //dont run if iscommentsenabled hasnt changed
		{
			if (!is_bool($comments))
			{
				return "Error occurred";
			}
		}

		if (getAssetInfo($placeid)->isGameWhitelisted != $whitelistenabled) //dont run if isGameWhitelisted hasnt changed
		{
			if (!is_bool($whitelistenabled))
			{
				return "Error occurred";
			}

			if ($whitelistenabled == 0) //whitelist being disabled
			{
				if (!Game::ClearWhitelist($placeid))
				{
					$whitelistenabled = 1;
				}
			}
		}

		if (getAssetInfo($placeid)->MaxPlayers != $players) //dont run if MaxPlayers hasnt changed
		{
			if ($players > 12 || $players < 1)
			{
				return "Error occurred";
			}
		}
		
		$configgame = $GLOBALS['pdo']->prepare("UPDATE assets SET Name = :name, Description = :description, isGameWhitelisted  = :pbswhitelistenabled, IsCommentsEnabled = :commentsenabled, MaxPlayers = :maxplayers WHERE id = :assetid");
		$configgame->bindParam(":name", $name, PDO::PARAM_STR);
		$configgame->bindParam(":description", $description, PDO::PARAM_STR);
		$configgame->bindParam(":pbswhitelistenabled", $whitelistenabled, PDO::PARAM_INT);
		$configgame->bindParam(":commentsenabled", $comments, PDO::PARAM_INT);
		$configgame->bindParam(":maxplayers", $players, PDO::PARAM_INT);
		$configgame->bindParam(":assetid", $placeid, PDO::PARAM_INT);
		$configgame->execute();
		return true;
	}
	return "No permission";
}

// ...

//asset functions

function availableAssetId() {
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
	$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
	$b->execute();
	return $b->rowCount() + 1;
	$GLOBALS['pdo']->exec("UNLOCK TABLES");
}

function createGenericAsset($assetid, $assettypeid, $targetid, $producttype, $name, $description, $creatorid, $price, $onsale, $ispublicdomain, $isapproved, $hash) {
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");

	$asset = $GLOBALS['pdo']->prepare("INSERT INTO assets (id, AssetTypeId, TargetId, ProductType, Name, Description, Created, Updated, CreatorId, PriceInAlphabux, IsForSale, isPublicDomain, isApproved, Hash) VALUES(:id, :AssetTypeId, :TargetId, :ProductType, :Name, :Description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :CreatorId, :PriceInAlphabux, :IsForSale, :isPublicDomain, :isApproved, :Hash)");
	$asset->bindParam(":id", $assetid, PDO::PARAM_INT);
	$asset->bindParam(":AssetTypeId", $assettypeid, PDO::PARAM_INT);
	$asset->bindParam(":TargetId", $targetid, PDO::PARAM_INT);
	$asset->bindParam(":ProductType", $producttype, PDO::PARAM_STR);
	$asset->bindParam(":Name", $name, PDO::PARAM_STR);
	$asset->bindParam(":Description", $description, PDO::PARAM_STR);
	$asset->bindParam(":CreatorId", $creatorid, PDO::PARAM_INT);
	$asset->bindParam(":isPublicDomain", $ispublicdomain, PDO::PARAM_INT);
	$asset->bindParam(":isApproved", $isapproved, PDO::PARAM_INT);
	$asset->bindParam(":Hash", $hash, PDO::PARAM_STR);
	$asset->bindParam(":PriceInAlphabux", $price, PDO::PARAM_INT);
	$asset->bindParam(":IsForSale", $onsale, PDO::PARAM_INT);
	$asset->execute();

	$GLOBALS['pdo']->exec("UNLOCK TABLES");
}

function convertAssetUrlToId($asseturl) {
	if (strpos($asseturl, "rbxassetid://") !== false) {
		return substr($asseturl, strpos($asseturl, "rbxassetid://")+13, strlen($asseturl));
	} else if (strpos($asseturl, "id=") !== false) {
		return substr($asseturl, strpos($asseturl, "id=")+3, strlen($asseturl));
	}
	return false;
}

function isMeshSupported($meshstr) {
	if (strpos($meshstr, "version 1.00") !== false || strpos($meshstr, "version 1.01") !== false || strpos($meshstr, "version 2.00") !== false) {
		return true;
	}
	return false;
}

function uploadXML($xml, $name, $description, $price, $onsale, $assettypeid, $creatorid) {
	$hash = genAssetHash(16);
	$assetid = availableAssetId();
	$name = cleanInput($name);

	if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $xml)) {
		createGenericAsset($assetid, $assettypeid, $assetid, "", $name, $description, $creatorid, $price, $onsale, 1, 1, $hash);
		return $assetid;
	}
	return false;
}

function uploadRobloxMesh($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 4) {
		$meshstr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
		if (isMeshSupported($meshstr)) {
			$hash = genAssetHash(16);
			$assetid = availableAssetId();
			$name = cleanInput($name) . " Mesh";

			if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $meshstr)) {
				createGenericAsset($assetid, 4, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
				return $assetid;
			}
		}
	}
	return false;
}

function uploadRobloxTexture($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 1) {
		$texturestr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
	
		$hash = genAssetHash(16);
		$assetid = availableAssetId();
		$name = cleanInput($name) . " Texture";

		if (file_put_contents($GLOBALS['thumbnailCDNPath'] . $hash, $texturestr)) {
			createGenericAsset($assetid, 1, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
			return $assetid;
		}
	}
	return false;
}

function uploadRobloxAnimation($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 24) {
		$animationstr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
	
		$hash = genAssetHash(16);
		$assetid = availableAssetId();
		$name = cleanInput($name);

		if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $animationstr)) {
			createGenericAsset($assetid, 24, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
			return $assetid;
		}
	}
	return false;
}

function submitRobloxAssetWorker($requestedassetid, $assettypeid, $assetname, $assetdescription, $price, $onsale) {
	//multiple occasions of the same item being uploaded, double check the name now
	$isduplicate = false;
	$duplicate = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE Name = :n");
	$duplicate->bindParam(":n", $assetname, PDO::PARAM_STR);
	$duplicate->execute();
	if ($duplicate->rowCount() > 0) {
		return "Asset exists with the same name";
	}

	$xml = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$requestedassetid);

	$validXML = true;
	try {
		$ParsedXML = new SimpleXMLElement($xml);
	} catch (Exception $e) {
		$validXML = false;
	}
						
	if ($validXML) {
		//handle all hat types
		if ($assettypeid == 41 || //HairAccessory
		$assettypeid == 42 || //FaceAccessory
		$assettypeid == 43 || //NeckAccessory
		$assettypeid == 44 || //ShoulderAccessory
		$assettypeid == 45 || //FrontAccessory
		$assettypeid == 46 || //BackAccessory
		$assettypeid == 47) { //WaistAccessory
			$assettypeid = 8; //we want all those variations above just to be a reg hat assettypeid
		}

		if ($assettypeid == 8 ||  //Hat
		$assettypeid == 18 || //Decal/Face
		$assettypeid == 19) { //Gear
			//currently only support texture and meshe auto uploading, TODO: audios and linked sources?

			$xmlhash = genAssetHash(16);

			//find two known xml elements that contain texture urls
			$textures = $ParsedXML->xpath('//Content[@name="TextureId"]/url | //Content[@name="Texture"]/url');

			//find mesh urls
			$meshes = $ParsedXML->xpath('//Content[@name="MeshId"]/url');

			$meshuploadsuccess = true;
			foreach ($meshes as $mesh) {
				$assetid = convertAssetUrlToId($mesh);
				if (!$assetid) {
					return "Unsupported Asset";
				}
				$assetid = uploadRobloxMesh($assetname, $assetid, 1);
				if ($assetid !== FALSE) {
					$xml=str_replace($mesh, $GLOBALS['url'] . "/asset/?id=" . $assetid, $xml);
					Render::RenderMesh($assetid);
				} else {
					$meshuploadsuccess = false;
					break;
				}
			}

			if (!$meshuploadsuccess && $assettypeid != 18) {
				return "Error occurred, one or more invalid Meshes";
			}

			$textureuploadsuccess = true;
			foreach ($textures as $texture) {
				$assetid = convertAssetUrlToId($texture);
				if (!$assetid) {
					return "Unsupported Asset";
				}
				$assetid = uploadRobloxTexture($assetname, $assetid, 1);
				if ($assetid !== FALSE) {
					$xml=str_replace($texture, $GLOBALS['url'] . "/asset/?id=" . $assetid, $xml);
				} else {
					$textureuploadsuccess = false;
					break;
				}
			}

			if (!$textureuploadsuccess) {
				return "Error occurred, one or more invalid Textures. Some meshes may have been uploaded";
			}

			$newassetid = uploadXML($xml, $assetname, $assetdescription, $price, $onsale, $assettypeid, 1);

			if ($newassetid !== FALSE) {
				giveItem(1, $newassetid); //give the user Alphaland the created asset
				$assettypeid = getAssetInfo($newassetid)->AssetTypeId;
			
				switch ($assettypeid) {
					case 8:
						Render::RenderHat($newassetid);
						break;
					case 18:
						Render::RenderFace($newassetid);
						break;
					case 19:
						Render::RenderGear($newassetid);
						break;
					default:
						break;
				}

				//discord bot api
				if ($onsale) {
					WebContextManager::HttpGetPing("localhost:4098/?type=itemrelease&assetid=".$newassetid."&name=".urlencode($assetname)."&description=".urlencode($assetdescription)."&price=".$price."&image=".$GLOBALS['renderCDN']."/".getAssetInfo($newassetid)->ThumbHash, 8000);
				}

				return true;
			}
			return "Error Occurred";
		}
		return "Invalid Asset";
	}
	return "Invalid XML";
}

function isAssetEquipped($assetid, $userid)
{
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE aid = :aid AND uid = :uid");
	$get->bindParam(":aid", $assetid, PDO::PARAM_INT);
	$get->bindParam(":uid", $userid, PDO::PARAM_INT);
	$get->execute();
	if ($get->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getUserGearsAccoutrements($userid) //ghetto
{
	$gears = "";
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM owned_assets WHERE uid = :u");
	$get->bindParam(":u", $userid, PDO::PARAM_INT);
	$get->execute();
	if($get->rowCount() > 0) {
		foreach($get as $item)  {
			if (getAssetInfo($item['aid'])->AssetTypeId == 19) {
				$equipped = "";
				if (isAssetEquipped($item['aid'], $userid)) {
					$equipped = "&equipped=1";
				}
				$gears .= $GLOBALS['url'].'/asset/?id='.$item['aid'].$equipped.';';
			}
		}
	}
	return $gears;
}

//TODO: FIX THIS DUMB SHIT
//file_get_contents adds &amp after every &
//2 lazy to look further into this right now, annoying as fuck
function getRobloxAssetThumbnail($assetid, $width, $height, $fileformat)
{
	return json_decode(file_get_contents($GLOBALS['ROBLOXAssetThumbnailAPI'].$assetid."&size=".$width."x".$height."&format=".$fileformat."&isCircular=false"))->data[0]->imageUrl;
}

function getRobloxProductInfo($assetid)
{
	$json = file_get_contents($GLOBALS['ROBLOXProductInfoAPI'].$assetid);
	return json_decode($json);
}

function ReturnAssetFromHash($hash) //asset CDN
{
	//alphaland assets cdn
	WebContextManager::Redirect(constructAssetHashUrl($hash));
}

function ReturnThumbnailFromHash($hash) //thumb CDN (images)
{
	//alphaland thumb (images) cdn
	WebContextManager::Redirect(constructThumbnailHashUrl($hash));
}

function CreateAsset($AssetTypeId, $IconImageAssetId, $TargetId, $ProductType, $Name, $Description, $Created, $Updated, $CreatorId, $PriceInAlphabux, $Sales, $isPersonalServer, $IsNew, $IsForSale, $IsPublicDomain, $IsLimited, $IsLimitedUnique, $IsCommentsEnabled, $IsApproved, $IsModerated, $Remaining, $MinimumMembershipLevel, $ContentRatingTypeId, $Favorited, $Visited, $MaxPlayers, $UpVotes, $DownVotes, $Hash, $ThumbHash)
{
	//setup the new asset in the DB, lock it!
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
	
	//assets increment
	$autoincrement = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
	$autoincrement->execute();
	$autoincrement = $autoincrement->rowCount() + 1; //initial auto increment value
	
	//db entry
	$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(Id, AssetTypeId, IconImageAssetId, TargetId, ProductType, Name, Description, Created, Updated, CreatorId, PriceInAlphabux, Sales, isPersonalServer, IsNew, IsForSale, IsPublicDomain, IsLimited, IsLimitedUnique, IsCommentsEnabled, IsApproved, IsModerated, Remaining, MinimumMembershipLevel, ContentRatingTypeId, Favorited, Visited, MaxPlayers, UpVotes, DownVotes,Hash,ThumbHash) VALUES (:Id, :AssetTypeId, :IconImageAssetId, :TargetId, :ProductType, :Name, :Description, :Created, :Updated, :CreatorId, :PriceInAlphabux, :Sales, :isPersonalServer, :IsNew, :IsForSale, :IsPublicDomain, :IsLimited, :IsLimitedUnique, :IsCommentsEnabled, :IsApproved, :IsModerated, :Remaining, :MinimumMembershipLevel, :ContentRatingTypeId, :Favorited, :Visited, :MaxPlayers, :UpVotes, :DownVotes, :Hash, :ThumbHash)");		
	$m->bindParam(":Id", $autoincrement, PDO::PARAM_INT);
	$m->bindParam(":AssetTypeId", $AssetTypeId, PDO::PARAM_INT);
	$m->bindParam(":IconImageAssetId", $IconImageAssetId, PDO::PARAM_INT);
	$m->bindParam(":TargetId", $autoincrement, PDO::PARAM_INT); //im not sure what to use this for so for now just use autoincrement
	$m->bindParam(":ProductType", $ProductType, PDO::PARAM_STR);
	$m->bindParam(":Name", $Name, PDO::PARAM_STR);
	$m->bindParam(":Description", $Description, PDO::PARAM_STR);
	$m->bindParam(":Created", $Created, PDO::PARAM_INT);
	$m->bindParam(":Updated", $Updated, PDO::PARAM_INT);
	$m->bindParam(":CreatorId", $CreatorId, PDO::PARAM_INT);
	$m->bindParam(":PriceInAlphabux", $PriceInAlphabux, PDO::PARAM_INT);
	$m->bindParam(":Sales", $Sales, PDO::PARAM_INT);
	$m->bindParam(":isPersonalServer", $isPersonalServer, PDO::PARAM_INT);
	$m->bindParam(":IsNew", $IsNew, PDO::PARAM_INT);
	$m->bindParam(":IsForSale", $IsForSale, PDO::PARAM_INT);
	$m->bindParam(":IsPublicDomain", $IsPublicDomain, PDO::PARAM_INT);
	$m->bindParam(":IsLimited", $IsLimited, PDO::PARAM_INT);
	$m->bindParam(":IsLimitedUnique", $IsLimitedUnique, PDO::PARAM_INT);
	$m->bindParam(":IsCommentsEnabled", $IsCommentsEnabled, PDO::PARAM_INT);
	$m->bindParam(":IsApproved", $IsApproved, PDO::PARAM_INT);
	$m->bindParam(":IsModerated", $IsModerated, PDO::PARAM_INT);
	$m->bindParam(":Remaining", $Remaining, PDO::PARAM_INT);
	$m->bindParam(":MinimumMembershipLevel", $MinimumMembershipLevel, PDO::PARAM_INT);
	$m->bindParam(":ContentRatingTypeId", $ContentRatingTypeId, PDO::PARAM_INT);
	$m->bindParam(":Favorited", $Favorited, PDO::PARAM_INT);
	$m->bindParam(":Visited", $Visited, PDO::PARAM_INT);
	$m->bindParam(":MaxPlayers", $MaxPlayers, PDO::PARAM_INT);
	$m->bindParam(":UpVotes", $UpVotes, PDO::PARAM_INT);
	$m->bindParam(":DownVotes", $DownVotes, PDO::PARAM_INT);
	$m->bindParam(":Hash", $Hash, PDO::PARAM_STR);
	$m->bindParam(":ThumbHash", $ThumbHash, PDO::PARAM_STR);
	$m->execute();
	
	$GLOBALS['pdo']->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
	
	return $autoincrement;
}

function setAssetModerated($id)
{
	$moderate = $GLOBALS['pdo']->prepare("UPDATE assets SET IsModerated = 1, IsApproved = 0, IsForSale = 0 WHERE id = :i");
	$moderate->bindParam(":i", $id, PDO::PARAM_INT);
	$moderate->execute();
}

function setAssetApproved($id)
{
	$approve = $GLOBALS['pdo']->prepare("UPDATE assets SET IsApproved = 1, IsModerated = 0 WHERE id = :i");
	$approve->bindParam(":i", $id, PDO::PARAM_INT);
	$approve->execute();
}

function approveAsset($id) //currently supports t-shirts, shirts and pants
{
	//the logic behind this is it uses the asset id of the item, then + 1 to get to the texture. We need a way to be sure the texture is always next, or a new way to detect a situation where the texture isn't next (a queue system for uploading assets?)
	
	$assettype = getAssetInfo($id)->AssetTypeId;
	
	$textureassetid = $id+1;

	setAssetApproved($id);
	setAssetApproved($textureassetid);

	if ($assettype == 1 && isImageAssetAssociatedWithBadge($id))
	{
		enableUserBadgeFromAssociatedImageAsset($id);
	}
	else
	{
		switch ($assettype)
		{
			case 2: //TShirt
				Render::RenderTShirt($id, true);
				break;
			case 11: //Shirt
				Render::RenderShirt($id, true);
				break;
			case 12: //Pants
				Render::RenderPants($id, true);
				break;
			default:
				break;
		}
	}
	return true;
}

function fetchPendingAssets($extraparams="")
{
	$pending = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE IsApproved = 0 AND IsModerated = 0 AND (AssetTypeId = 1 AND Description = 'Place Thumbnail' OR AssetTypeId = 1 AND Description = 'Badge Image' OR AssetTypeId = 2 OR AssetTypeId = 11 OR AssetTypeId = 12 OR AssetTypeId = 22 OR AssetTypeId = 21) ".$extraparams."");
	$pending->execute();
	return $pending;
}

function moderateAsset($id) //currently supports t-shirts, shirts and pants
{
	$rendercdn = $GLOBALS['renderCDNPath'];
	$thumbscdn = $GLOBALS['thumbnailCDNPath'];
	$assetscdn = $GLOBALS['assetCDNPath'];
	
	$assetinfo = getAssetInfo($id);
	$assetData = file_get_contents($assetscdn . $assetinfo->Hash);

	//first handle assets that have no dependencies
	if ($assetinfo->AssetTypeId == 22) //group emblem
	{
		unlink($thumbscdn . $assetinfo->Hash);
		setAssetModerated($id);
		return true;
	}
	else
	{
		$validXML = true;
		try
		{
			$ParsedXML = new SimpleXMLElement($assetData);
		}
		catch (Exception $e)
		{
			$validXML = false;
		}

		if ($validXML)
		{
			$userCosmetic = false;
			$userModel = false;
			$userSolidModel = false;
			$textureUrl = "";
			$textureAssetId = "";
			$textureHash = "";
			$assetHash = "";
			$assetThumbHash = "";

			switch ($assetinfo->AssetTypeId) 
			{
				case 2: //tshirt
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="Graphic"]/url')[0];
					break;
				case 11: //shirt
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="ShirtTemplate"]/url')[0];
					break;
				case 12: //pants
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="PantsTemplate"]/url')[0];
					break;
				case 39: //solid model
					$userSolidModel = true;
					return "SolidModel Moderation coming soon";
				case 10: //model
					$userModel = true;
					return "Model Moderation coming soon";
				default:
					return "Unsupported Asset";
			}

			if ($userCosmetic)
			{
				if (strpos($textureUrl, "id="))
				{
					$textureAssetId = substr($textureUrl, strpos($textureUrl, "id=")+3, strlen($textureUrl));
					$textureHash = getAssetInfo($textureAssetId)->Hash;
					$assetHash = getAssetInfo($id)->Hash;
					$assetThumbHash = getAssetInfo($id)->ThumbHash;
				}
				else
				{
					return "Failed to fetch the Texture, contact an Administrator";
				}
			}

			//update the texture hash to blank, description to Content Deleted, name to Content Deleted, IsForSale to 0 and IsCommentsEnabled to 0
			$updatetexture = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '', Name = 'Content Deleted', Description = 'Content Deleted', IsForSale = 0, IsCommentsEnabled = 0 WHERE id = :i");
			$updatetexture->bindParam(":i", $textureAssetId, PDO::PARAM_INT);
			$updatetexture->execute();

			//update the shirt asset hash to blank and set the thumbhash to blank, description to Content Deleted, name to Content Deleted, IsForSale to 0 and IsCommentsEnabled to 0
			$updatecosmetic = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '', ThumbHash = '', Name = 'Content Deleted', Description = 'Content Deleted', IsForSale = 0, IsCommentsEnabled = 0 WHERE id = :i");
			$updatecosmetic->bindParam(":i", $id, PDO::PARAM_INT);
			$updatecosmetic->execute();

			//delete the texture asset, shirt asset and shirt thumb hash from the cdn
			unlink($thumbscdn . $textureHash);
			unlink($assetscdn . $assetHash);
			unlink($rendercdn . $assetThumbHash);

			setAssetModerated($id);
			setAssetModerated($textureAssetId);

			//re render players wearing the asset (if its a tshirt, shirt or pants)
			if ($assetinfo->AssetTypeId == 2 || $assetinfo->AssetTypeId == 11 || $assetinfo->AssetTypeId == 12)
			{
				$assetowners = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE aid = :a");
				$assetowners->bindParam(":a", $id, PDO::PARAM_INT);
				$assetowners->execute();
				
				foreach($assetowners as $owner)
				{
					UsersRender::RenderPlayer($owner['uid']);
					Sleep(2);
				}
			}

			return true;
		}
	}
	return "Error Occurred";
}

function isAssetApproved($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i AND IsApproved = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function isAssetModerated($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i AND IsModerated = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getAssetInfo($id) 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}

function assetTypeArray()
{
	$types = array(	
	0  => "Product",
	1  => "Image",
    2  => "T-Shirt",
    3  => "Audio",
    4  => "Mesh",
    5  => "Lua",
    6  => "HTML",
    7  => "Text",
    8  => "Hat",
    9  => "Place",
    10 => "Model",
    11 => "Shirt",
    12 => "Pants",
    13 => "Decal",
    16 => "Avatar",
    17 => "Head",
    18 => "Face",
    19 => "Gear",
    21 => "Badge",
    22 => "Group Emblem",
    24 => "Animation",
    25 => "Arms",
    26 => "Legs",
    27 => "Torso",
    28 => "Right Arm",
    29 => "Left Arm",
    30 => "Left Leg",
    31 => "Right Leg",
    32 => "Package",
    33 => "YouTube Video",
    34 => "Game Pass",
	35 => "App",
	37 => "Code",
	38 => "Plugin",
	39 => "SolidModel",
	40 => "MeshPart"
	);	
	return $types;
}

function isWearable($itemtype)
{
	if ($itemtype == 8 || //hats
	$itemtype == 2 || //tshirts
	$itemtype == 11 || //shirts
	$itemtype == 12 || //pants
	$itemtype == 18 || //faces
	$itemtype == 19 || //gears
	$itemtype == 17 || //heads
	$itemtype == 32) //packages
	{
		return true;
	}
	return false;
}

function typeToMaxCosmetic($itemtype) //itemtype 8 = hats, 2 = tshirts, 11 = shirts, 12 = pants, 18 = faces, 19 = gears, 17 = heads, 32 = packages
{
	switch ($itemtype) {
		case 8: //hats
			return 6;
		case 2: //tshirts
			return 1;
		case 11: //shirts
			return 1;
		case 12: //pants
			return 1;
		case 18: //faces
			return 1;
		case 19: //gears
			return 1;
		case 17: //heads
			return 1;
		case 32: //packages
			return 1;
		default: //what?
			return 0;
	}
}

//end asset functions

//user functions

function chatFilterInfractionLimit($userid, $limit, $seconds)
{
	$infractions = $GLOBALS['pdo']->prepare("SELECT * FROM chat_logs WHERE whoSent = :uid AND (whenSent + :seconds) > UNIX_TIMESTAMP() AND trippedFilter = 1");
	$infractions->bindParam(":uid", $userid, PDO::PARAM_INT);
	$infractions->bindParam(":seconds", $seconds, PDO::PARAM_INT);
	$infractions->execute();
	if ($infractions->rowCount() >= $limit)
	{
		return true;
	}
	return false;
}

function kickUserIfInGame($userid, $message)
{
	$gamesession = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
	$gamesession->bindParam(":i", $userid, PDO::PARAM_INT);
	$gamesession->execute();

	if ($gamesession->rowCount() > 0)
	{
		soapExecuteEx($GLOBALS['gamesArbiter'], $gamesession->fetch(PDO::FETCH_OBJ)->jobid, "Kick Message ".$userid, "game.Players." . getUsername($userid) . ":Kick(\"".$message."\")");
	}
}

// ...

//friend request button check

function friendStatus($userid)
{
	if(friendsWith($userid))
	{
		return "Friends";
	} 
	elseif(friendsPending($userid))
	{
		return "Pending";
	}
	elseif(userSentFriendReq($userid))
	{
		return "Incoming";
	}
	elseif ($GLOBALS['user']->id != $userid)
	{
		return "User";
	}
	return "";
}

//end user functions

//asset comments stuff {
	
function placeAssetComment($aid, $comment) //1 = comment placed, 2 = cooldown, 3 = error
{
	$interval = 0;
	$localuser = $GLOBALS['user']->id;
	
	$commentscheck = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$commentscheck->bindParam(":i", $aid, PDO::PARAM_INT);
	$commentscheck->execute();
	$commentscheck = $commentscheck->fetch(PDO::FETCH_OBJ);
		
	if ($commentscheck->IsCommentsEnabled == true) //check if comments are enabled for the asset
	{
		$intervalcheck = $GLOBALS['pdo']->prepare("SELECT * FROM asset_comments WHERE uid = :u ORDER BY whenCommented DESC LIMIT 1");
		$intervalcheck->bindParam(":u", $localuser, PDO::PARAM_INT);
		$intervalcheck->execute();
			
		if ($intervalcheck->rowCount() > 0) //we dont want to be calling an object that is NULL
		{
			$interval = (int)$intervalcheck->fetch(PDO::FETCH_OBJ)->whenCommented;
		}
			
		if(($interval + (60)) < time()) //60 second interval
		{
			if(strlen($comment) < 5)
			{
				return "Comment too short, must be above 5 Characters";
			}
			elseif(strlen($comment) > 200)
			{
				return "Comment too long, must be under 200 Characters";
			}
			else
			{
				$setcomment = $GLOBALS['pdo']->prepare("INSERT INTO asset_comments(uid, aid, comment, whenCommented) VALUES(:u, :aid, :c, UNIX_TIMESTAMP())");
				$setcomment->bindParam(":u", $localuser, PDO::PARAM_INT);
				$setcomment->bindParam(":aid", $aid, PDO::PARAM_INT);
				$setcomment->bindParam(":c", $comment, PDO::PARAM_INT);
				if ($setcomment->execute())
				{
					return true;
				}
				return "Error Occurred";
			}
		}
		return "Please wait before commenting again";
	}
	return "Error Occurred";
}

//end catalog comments stuff }

//canjoin stuff {
	
function canJoinUser($uid) //
{
	$canjoinstatusquery = $GLOBALS['pdo']->prepare("SELECT canJoin FROM users WHERE id = :i");
	$canjoinstatusquery->bindParam(":i", $uid, PDO::PARAM_INT);
	$canjoinstatusquery->execute();
	$canjoinstatus = (int)$canjoinstatusquery->fetch(PDO::FETCH_OBJ)->canJoin;
	
	//0 = noone, 1 = friends, 2 = everyone
	if($canjoinstatus == 1)
	{
		if (friendsWith($uid))
		{
			return true;
		}
	}
	elseif($canjoinstatus == 2)
	{
		return true;
	}
	return false;
}
	
//end canjoin stuff }

//email stuff {
	
function sendMail($from, $recipient, $subject, $body, $altbody)
{
	$job = popen("cd C:/Webserver/nginx/Alphaland && start /B php sendEmail.php ".$from." ".$recipient." ".base64_encode($subject)." ".base64_encode($body)." ".base64_encode($altbody), "r"); 
    if ($job !== FALSE);
    {
        pclose($job);
        return true;
    }
    return false;
}
	
function verifyEmail($token)
{
	$localuser = $GLOBALS['user']->id;
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE uid = :u AND token = :t AND valid = 1");
	$check->bindParam(":u", $localuser, PDO::PARAM_INT);
	$check->bindParam(":t", $token, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //verify email if everything checks out
	{
		$verify = $GLOBALS['pdo']->prepare("UPDATE users SET verified = 1 WHERE id = :u"); //verify user
		$verify->bindParam(":u", $localuser, PDO::PARAM_INT);
		$verify->execute();
			
		$invalidatekey = $GLOBALS['pdo']->prepare("DELETE from verify_email_keys WHERE uid = :u"); //delete db key
		$invalidatekey->bindParam(":u", $localuser, PDO::PARAM_INT);
		$invalidatekey->execute();
		
		//verified badge
		$verifiedbadgeid = 1;
		$checkifbadgeexist = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :u AND bid = :b");
		$checkifbadgeexist->bindParam(":u", $localuser, PDO::PARAM_INT);
		$checkifbadgeexist->bindParam(":b", $verifiedbadgeid, PDO::PARAM_INT);
		$checkifbadgeexist->execute();
		if ($checkifbadgeexist->rowCount() == 0)
		{
			$verifiedbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 1, UNIX_TIMESTAMP())");
			$verifiedbadge->bindParam(":n", $localuser, PDO::PARAM_INT);
			$verifiedbadge->bindParam(":d", $verifiedbadgeid, PDO::PARAM_INT);
			$verifiedbadge->execute();
		}	
		// ...
		return true;
	}
	return false;
}
	
function sendVerificationEmail($from, $recipient) //1 = success, 2 = cooldown, 3 = fail
{
	$localuser = $GLOBALS['user']->id;
	
	$t = genVerifcationEmailHash(24);
	
	$email_html = '
	<div style="width:40rem;background-color:white;border:1px solid rgba(0,0,0,.125);border-radius:5px;padding:12px;margin:auto;">
		<div style="text-align:center;">
			<img src="https://alphaland.cc/alphaland/cdn/imgs/alphaland-1024.png" style="width:18rem;">
		</div>
		<div style="text-align:center;">
			<strong>Click the button below to verify your email!</strong>
		</div>
		<div style="text-align:center;margin-top:10px;">
			<a style="text-decoration:none;" href="https://alphaland.cc/verifyemail?token='.$t.'"><span style="border:none;color:white;background-color:#c82333;border-radius:4px;padding:10px;cursor:pointer;">Verify Email</span></a>
		</div>
	</div>';
	
	$email_altbody = 'https://alphaland.cc/verifyemail?token='.$t.'';
	
	$checkifverified = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u AND verified = 0");
	$checkifverified->bindParam(":u", $localuser, PDO::PARAM_INT);
	$checkifverified->execute();
	
	if ($checkifverified->rowCount() > 0) //player isnt verified
	{
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE uid = :u AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->execute();
		
		if ($check->rowCount() > 0) //already a verification email pending
		{
			$keyinfo = $check->fetch(PDO::FETCH_OBJ);
			
			if(($keyinfo->lastAttempt + (60)) < time()) //under the 60 second interval
			{
				$updatetoken = $GLOBALS['pdo']->prepare("UPDATE verify_email_keys SET lastAttempt = UNIX_TIMESTAMP(), token = :t WHERE uid = :u AND valid = 1");
				$updatetoken->bindParam(":t", $t, PDO::PARAM_INT);
				$updatetoken->bindParam(":u", $localuser, PDO::PARAM_INT);
				$updatetoken->execute();
				
				if (sendMail($from, $recipient, "Verify Email", $email_html, $email_altbody))
				{
					return 1;
				}
			}
			else
			{
				return 2;
			}
		}
		else //create new one
		{
			$n = $GLOBALS['pdo']->prepare("INSERT INTO verify_email_keys(uid, token, valid, lastAttempt) VALUES(:u, :t, 1, UNIX_TIMESTAMP())");
			$n->bindParam(":u", $localuser, PDO::PARAM_INT);
			$n->bindParam(":t", $t, PDO::PARAM_INT);
			if($n->execute()) 
			{
				if (sendMail($from, $recipient, "Verify Email", $email_html, $email_altbody))
				{
					return 1;
				}
			}
		}	
	}
	return 3;
}

function changeEmail($newemail) //1 = success, 2 = cooldown, 3 = fail
{
	$localuser = $GLOBALS['user']->id;
	
	$sendver = sendVerificationEmail("info@alphaland.cc", $newemail);
	
	if ($sendver == 1) //all good
	{
		$updateemail = $GLOBALS['pdo']->prepare("UPDATE users SET email = :e WHERE id = :u");
		$updateemail->bindParam(":e", $newemail, PDO::PARAM_STR);
		$updateemail->bindParam(":u", $localuser, PDO::PARAM_INT);
		$updateemail->execute();
		return 1;
	}
	elseif ($sendver == 2) //cooldown
	{
		return 2;
	}
	elseif ($sendver == 3) //player is verified, do it again but unverify first
	{
		$unverify = $GLOBALS['pdo']->prepare("UPDATE users SET verified = 0 WHERE id = :u");
		$unverify->bindParam(":u", $localuser, PDO::PARAM_INT);
		if ($unverify->execute())
		{
			$sendver2 = sendVerificationEmail("info@alphaland.cc", $newemail);
			
			if ($sendver2 == 1) //all good
			{
				$updateemail = $GLOBALS['pdo']->prepare("UPDATE users SET email = :e WHERE id = :u");
				$updateemail->bindParam(":e", $newemail, PDO::PARAM_STR);
				$updateemail->bindParam(":u", $localuser, PDO::PARAM_INT);
				$updateemail->execute();
				return 1;
			}
			elseif ($sendver2 == 2) //cooldown
			{
				return 2;
			}
			elseif ($sendver2 == 3) //still didnt go thru, return error
			{
				return 3;
			}
		}
		else //couldnt unverify, return error
		{
			return 3;
		}
	}
}

function changePassword($newpass)
{
	$localuser = $GLOBALS['user']->id;
	
	$encryptedpassword = password_hash($newpass, PASSWORD_DEFAULT);
	
	$updatepassword = $GLOBALS['pdo']->prepare("UPDATE users SET pwd = :p WHERE id = :u");
	$updatepassword->bindParam(":p", $encryptedpassword, PDO::PARAM_STR);
	$updatepassword->bindParam(":u", $localuser, PDO::PARAM_INT);
	if ($updatepassword->execute())
	{
		return true;
	}
	return false;
}

function changePasswordUid($uid, $newpass)
{
	$encryptedpassword = password_hash($newpass, PASSWORD_DEFAULT);
	
	$updatepassword = $GLOBALS['pdo']->prepare("UPDATE users SET pwd = :p WHERE id = :u");
	$updatepassword->bindParam(":p", $encryptedpassword, PDO::PARAM_STR);
	$updatepassword->bindParam(":u", $uid, PDO::PARAM_INT);
	if ($updatepassword->execute())
	{
		return true;
	}
	return false;
}

function confirmPasswordReset($token) //0 = error, > 0 = good
{
	//echo $token;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :t AND valid = 1");
	$check->bindParam(":t", $token, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //change password if everything checks out
	{
		$userdetails2 = $check->fetch(PDO::FETCH_OBJ);
		$invalidatekey = $GLOBALS['pdo']->prepare("DELETE from password_reset_keys WHERE token = :u"); //delete db key
		$invalidatekey->bindParam(":u", $token, PDO::PARAM_INT);
		$invalidatekey->execute();
		return $userdetails2->uid;
	}
	return 0;
}

function sendPasswordReset($from, $recipient, $recipientuid) //1 = success, 2 = cooldown, 3 = fail
{
	$t = genResetPasswordHash(24);
	
	$email_html = '
	<div style="width:40rem;background-color:white;border:1px solid rgba(0,0,0,.125);border-radius:5px;padding:12px;margin:auto;">
		<div style="text-align:center;">
			<img src="https://alphaland.cc/alphaland/cdn/imgs/alphaland-1024.png" style="width:18rem;">
		</div>
		<div style="text-align:center;">
			<strong>Click the button below to reset your password!</strong>
		</div>
		<div style="text-align:center;margin-top:10px;">
			<a style="text-decoration:none;" href="https://alphaland.cc/settings/resetpassword?token='.$t.'"><span style="border:none;color:white;background-color:#c82333;border-radius:4px;padding:10px;cursor:pointer;">Reset Password</span></a>
		</div>
	</div>';
	
	$email_altbody = 'https://alphaland.cc/settings/resetpassword?token='.$t.'';
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE uid = :u AND valid = 1");
	$check->bindParam(":u", $recipientuid, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //already a pass reset email pending
	{
		$keyinfo = $check->fetch(PDO::FETCH_OBJ);
			
		if(($keyinfo->lastAttempt + (60)) < time()) //under the 60 second interval
		{
			$updatetoken = $GLOBALS['pdo']->prepare("UPDATE password_reset_keys SET lastAttempt = UNIX_TIMESTAMP(), token = :t WHERE uid = :u AND valid = 1");
			$updatetoken->bindParam(":t", $t, PDO::PARAM_INT);
			$updatetoken->bindParam(":u", $recipientuid, PDO::PARAM_INT);
			$updatetoken->execute();
				
			if (sendMail($from, $recipient, "Reset Password", $email_html, $email_altbody))
			{
				return 1;
			}
		}
		else
		{
			return 2;
		}
	}
	else //create new one
	{
		$n = $GLOBALS['pdo']->prepare("INSERT INTO password_reset_keys(uid, token, valid, lastAttempt) VALUES(:u, :t, 1, UNIX_TIMESTAMP())");
		$n->bindParam(":u", $recipientuid, PDO::PARAM_INT);
		$n->bindParam(":t", $t, PDO::PARAM_INT);
		if($n->execute()) 
		{
			if (sendMail($from, $recipient, "Reset Password", $email_html, $email_altbody))
			{
				return 1;
			}
		}
	}	
	return 3;
}
	
//email stuff end }
	
//signup keys end }

//badges {
	
function allOfficialBadges() {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM official_badges");
	$check->execute();
	return $check;
}
	
function officialBadgeInfo($id) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM official_badges WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}
	
function officialPlayerBadges($id) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

/////////////////

function getUserBadgeInfo($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}

function getUserBadgeOwner($id)
{
	return getAssetInfo(getUserBadgeInfo($id)->AwardingPlaceID)->CreatorId;
}

function isImageAssetAssociatedWithBadge($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE BadgeImageAssetID = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getUserBadgeImage($id)
{
	return getAssetRender(getUserBadgeInfo($id)->BadgeImageAssetID);
}

function enableUserBadge($id)
{
	$check = $GLOBALS['pdo']->prepare("UPDATE badges SET isEnabled = 1 WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
}

function enableUserBadgeFromAssociatedImageAsset($id)
{
	$badge = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE BadgeImageAssetID = :i");
	$badge->bindParam(":i", $id, PDO::PARAM_INT);
	$badge->execute();
	$badge = $badge->fetch(PDO::FETCH_OBJ)->id;
	enableUserBadge($badge);
}

function getPlayerBadges($userid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 0");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function hasUserBadge($userid, $badgeid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND bid = :bid AND isOfficial = 0");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->bindParam(":bid", $badgeid, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function rewardUserBadge($UserID, $BadgeID, $PlaceID)
{
	$badge = getUserBadgeInfo($BadgeID);
	if ($badge !== FALSE && $badge->AwardingPlaceID == $PlaceID) //badge exists and the placeid matches the badges awardingplaceid
	{
		if (!hasUserBadge($UserID, $BadgeID)) //user doesnt already have the badge
		{
			$rbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 0, UNIX_TIMESTAMP())");
			$rbadge->bindParam(":n", $UserID, PDO::PARAM_INT);
			$rbadge->bindParam(":d", $BadgeID, PDO::PARAM_INT);
			$rbadge->execute();
			return true;
		}
	}
	return false;
}
		
//end of badges }

//backend communication and utilities for jobs {

function isThumbnailerAlive() //the main portion of this check is now a background script
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM websettings WHERE isThumbnailerAlive = 1");
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function verifyLuaValue($value) //mostly due to booleans, but maybe something will come up in the future
{
	switch ($value)
	{
		case is_bool(json_encode($value)) || $value == 1:
			return json_encode($value);
		default:
			return $value;
	}
}

function getLuaType($value) //currently only supports booleans, integers and strings
{
	switch ($value)
	{
		case $value == "true" || $value == "false": //this is so gay
			return "LUA_TBOOLEAN";
		case !is_string($value) && !is_bool($value) && filter_var($value, FILTER_VALIDATE_INT):
			return "LUA_TNUMBER";
		default:
			return "LUA_TSTRING";
	}
}

function luaArguments($arguments=[]) //arguments for a script being executed
{
	if (!empty($arguments)) {
		$luavalue = array("LuaValue"=>array());
		foreach ($arguments as $argument) { 
			array_push($luavalue['LuaValue'], array(
				"type" => getLuaType($argument),
				"value" => verifyLuaValue($argument)
			));
		}
		return $luavalue;
	}
}

function soapCloseAllJobs($arbiter)
{
	return soapCallService($arbiter, "CloseAllJobs");
}

function soapExecuteEx($arbiter, $jobid, $scriptname, $script, $arguments=[])
{
	return soapCallService($arbiter, "ExecuteEx", array(
			"jobID" => $jobid, 
			"script" => array(
				"name" => $scriptname,
				"script" => $script,
				"arguments" => luaArguments($arguments)
			)
		)
	);
}

function soapCallService($arbiter, $name, $arguments = []) 
{
	$soapcl = new \SoapClient($GLOBALS['RCCwsdl'], ["location" => "http://".$arbiter, "uri" => "http://roblox.com/", "exceptions" => false]);
	return $soapcl->{$name}($arguments); //thanks BrentDaMage didnt know u can do this
}

//end backend communication }

//thumbnails portion {

function constructRenderHashUrl($hash)
{
	return $GLOBALS['renderCDN']."/".$hash;
}

function constructThumbnailHashUrl($hash)
{
	return $GLOBALS['thumbnailCDN']."/".$hash;
}

function constructAssetHashUrl($hash)
{
	return $GLOBALS['assetCDN']."/".$hash;
}

function getSPTCosmeticTexture($id) //get shirt, pants or t shirt texture (used for asset approval system)
{
	//this is going by the logic that every shirt,pants and tshirt cosmetic will have the texture after the xml's asset id
	$id = $id + 1;
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	$thumbhash = $check->Hash;
	
	return constructThumbnailHashUrl($thumbhash);
}

function getImageFromAsset($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	$thumbhash = $check->Hash;
	
	return constructThumbnailHashUrl($thumbhash);
}

function getPlayerRender($uid, $headshot=false)
{
	//check if the user has a stalled render
	UsersRender::PendingRender($uid);
	
	$player = userInfo($uid);

	if ($player !== FALSE)
	{	
		if ($headshot && !empty($player->HeadshotThumbHash))
		{
			if (file_exists($GLOBALS['renderCDNPath'] . $player->HeadshotThumbHash))
			{
				return constructRenderHashUrl($player->HeadshotThumbHash); 
			}
		}
		else
		{
			if (file_exists($GLOBALS['renderCDNPath'] . $player->ThumbHash))
			{
				return constructRenderHashUrl($player->ThumbHash);
			}
		}
	}
	return getImageFromAsset(229); //229 is the pending render image
}

function getAssetRender($id)
{
	$assetinfo = getAssetInfo($id); //asset info

	if ($assetinfo !== FALSE)
	{
		if ($assetinfo->IsModerated == true)
		{
			return getImageFromAsset(193); //193 is moderated asset image
		}
		elseif ($assetinfo->IsApproved == false)
		{
			return getImageFromAsset(194); //194 is pending asset image
		}
		else
		{
			if ($assetinfo->AssetTypeId == 1 || $assetinfo->AssetTypeId == 22) //images and group emblems
			{
				$assethash = $assetinfo->Hash;
				if (file_exists($GLOBALS['thumbnailCDNPath'].$assethash))  {
					return constructThumbnailHashUrl($assethash);
				}	
			}
			else //default to grab the assets ThumbHash
			{
				if (!empty($assetinfo->ThumbHash)) //if a render was ever performed
				{
					$thumbhash = $assetinfo->ThumbHash;
					if (file_exists($GLOBALS['renderCDNPath'].$thumbhash))  {
						return constructRenderHashUrl($thumbhash);
					}
				}
			}
		}
	}
	return getImageFromAsset(126); //126 is default image asset id
}

function setPlaceUsingCustomThumbnail($id)
{
	$check = $GLOBALS['pdo']->prepare("UPDATE assets SET isPlaceRendered = 0, ThumbHash = NULL WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
}

function isPlaceUsingRender($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	if ($check->isPlaceRendered == 1)
	{
		return true;
	}
	return false;
}

function usingCustomThumbnail($id) //so the thumb doesnt break while rendering
{
	//first grab the place iconimageassetid
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	$thumbhash = $check->ThumbHash;
	if (file_exists($GLOBALS['thumbnailCDNPath'] . $thumbhash))
	{
		return true;
	}
	return false;
}

function handleRenderPlace($placeid) //we have a 60 second wait, and we verify the render job was sent.
{
	$assetinfo = getAssetInfo($placeid);
	$lastrender = $assetinfo->lastPlaceRender;
			
	if(($lastrender + (60)) < time()) //60 second interval
	{
		if (Render::RenderPlace($placeid))
		{
			$c = $GLOBALS['pdo']->prepare("UPDATE assets SET lastPlaceRender = UNIX_TIMESTAMP() WHERE id = :i");
			$c->bindParam(":i", $placeid, PDO::PARAM_INT); //place id
			$c->execute();

			return true;
		}
		return "Failed to render Place";
	}
	return "Please wait before switching Thumbnail Settings again";
}

function handleGameThumb($id)
{
	$placeinfo = getAssetInfo($id);

	//see if the place is using rendered thumb
	$thumbhash = $placeinfo->ThumbHash;
	if ($thumbhash)
	{
		//broken check
		if (!file_get_contents($GLOBALS['renderCDNPath'].$thumbhash))
		{
			return getImageFromAsset(4);
		}
		return constructRenderHashUrl($thumbhash);
	}

	//get the iconimageassetid
	$iconimageassetid = getAssetInfo($placeinfo->IconImageAssetId); 

	if ($iconimageassetid->IsModerated == true)
	{
		return getImageFromAsset(193); //193 is moderated asset image
	}
	elseif ($iconimageassetid->IsApproved == false)
	{
		return getImageFromAsset(194); //194 is pending asset image
	}
	else
	{
		return constructThumbnailHashUrl($iconimageassetid->Hash);
	}	
}
//end thumbnails portion }

//character colors portion {
	
function getBC($id) {
	$bc = array("1" => "rgb(242, 243, 243)", "5" => "rgb(215, 197, 154)", "9" => "rgb(232, 186, 200)", "11" => "rgb(128, 187, 219)", "18" => "rgb(204, 142, 105)", "21" => "rgb(196, 40, 28)", "23" => "rgb(13, 105, 172)", "24" => "rgb(245, 208, 48)", "26" => "rgb(27, 42, 53)", "28" => "rgb(40, 127, 71)", "29" => "rgb(161, 196, 140)", "37" => "rgb(75, 151, 75)", "38" => "rgb(160, 95, 53)", "45" => "rgb(180, 210, 228)", "101" => "rgb(218, 134, 122)", "102" => "rgb(110, 153, 202)", "104" => "rgb(107, 50, 124)", "105" => "rgb(226, 155, 64)", "106" => "rgb(218, 133, 65)", "107" => "rgb(0, 143, 156)", "119" => "rgb(164, 189, 71)", "125" => "rgb(234, 184, 146)", "135" => "rgb(116, 134, 157)", "141" => "rgb(39, 70, 45)", "151" => "rgb(120, 144, 130)", "153" => "rgb(149, 121, 119)", "192" => "rgb(105, 64, 40)", "194" => "rgb(163, 162, 165)", "199" => "rgb(99, 95, 98)", "208" => "rgb(229, 228, 223)", "217" => "rgb(124, 92, 70)", "226" => "rgb(253, 234, 141)", "360" => "rgb(150, 103, 102)", "1001" => "rgb(248, 248, 248)", "1002" => "rgb(205, 205, 205)", "1003" => "rgb(17, 17, 17)", "1004" => "rgb(255, 0, 0)", "1005" => "rgb(255, 176, 0)", "1006" => "rgb(180, 128, 255)", "1007" => "rgb(163, 75, 75)", "1008" => "rgb(193, 190, 66)", "1009" => "rgb(255, 255, 0)", "1010" => "rgb(0, 0, 255)", "1011" => "rgb(0, 32, 96)", "1012" => "rgb(33, 84, 185)", "1013" => "rgb(4, 175, 236)", "1014" => "rgb(170, 85, 0)", "1015" => "rgb(170, 0, 170)", "1016" => "rgb(255, 102, 204)", "1018" => "rgb(18, 238, 212)", "1019" => "rgb(0, 255, 255)", "1020" => "rgb(0, 255, 0)", "1021" => "rgb(58, 125, 21)", "1022" => "rgb(127, 142, 100)", "1023" => "rgb(140, 91, 159)", "1024" => "rgb(175, 221, 255)", "1025" => "rgb(255, 201, 201)", "1026" => "rgb(177, 167, 255)", "1027" => "rgb(159, 243, 233)", "1028" => "rgb(204, 255, 204)", "1029" => "rgb(255, 255, 204)", "1030" => "rgb(255, 204, 153)", "1031" => "rgb(98, 37, 209)", "1032" => "rgb(255, 0, 191)");
	return ((array_key_exists($id, $bc))? $bc[$id]:"-");
}

//end character colors portion }

//settings portion {

function setBlurb($newblurb)
{
	$newblurb = cleanInput($newblurb);
	if (strlen($newblurb)<=4096) //limit 4096 characters
	{
		$localplayer = $GLOBALS['user']->id;
		
		$data = ['blurb' => $newblurb, 'id' => $localplayer];
		
		$blurb = $GLOBALS['pdo']->prepare("UPDATE users SET blurb=:blurb WHERE id=:id");
		if ($blurb->execute($data))
		{
			return true;
		}
	}
	return false;
}
	
//end settings portion }

//player appearance/inventory/purchases portion {
	
function setDefaults($uid) //gives default shirt and pants, body colors and wears the shirt and pants
{
	$check = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 133, 0, UNIX_TIMESTAMP(), 1)"); //give asset 133
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->execute();
	
	$check2 = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 135, 0, UNIX_TIMESTAMP(), 1)"); //give asset 135
	$check2->bindParam(":u", $uid, PDO::PARAM_INT);
	$check2->execute();
	
	$check3 = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 1, 0, UNIX_TIMESTAMP(), 1)"); //give asset 1
	$check3->bindParam(":u", $uid, PDO::PARAM_INT);
	$check3->execute();
	
	$check4 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 133, UNIX_TIMESTAMP())"); //wear asset 133
	$check4->bindParam(":u", $uid, PDO::PARAM_INT);
	$check4->execute();
	
	$check5 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 135, UNIX_TIMESTAMP())"); //wear asset 135
	$check5->bindParam(":u", $uid, PDO::PARAM_INT);
	$check5->execute();
	
	$check6 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 1, UNIX_TIMESTAMP())"); //wear asset 1
	$check6->bindParam(":u", $uid, PDO::PARAM_INT);
	$check6->execute();
	
	$check7 = $GLOBALS['pdo']->prepare("INSERT into body_colours (uid) VALUES(:u)"); //body colors (we just need a uid since the default is in the db)
	$check7->bindParam(":u", $uid, PDO::PARAM_INT);
	$check7->execute();

	$defaulthash = $GLOBALS['defaultOutfitHash']; //default outfit hash
	$defaultheadshothash = $GLOBALS['defaultHeadshotHash']; //default headshot hash
	$check8 = $GLOBALS['pdo']->prepare("UPDATE users SET ThumbHash = :dh, HeadshotThumbHash = :hdh WHERE id = :u");
	$check8->bindParam(":dh", $defaulthash, PDO::PARAM_STR);
	$check8->bindParam(":hdh", $defaultheadshothash, PDO::PARAM_STR);
	$check8->bindParam(":u", $uid, PDO::PARAM_INT);
	$check8->execute();
}

function itemSalesCount($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	return $check->Sales;
}

function giveCurrency($amount, $userid)
{
	//log the transaction
	$info = "Gave user ".$userid." ".$amount;
	$log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
	$log->bindParam(":info", $info, PDO::PARAM_STR);
	$log->bindParam(":amount", $amount, PDO::PARAM_INT);
	$log->bindParam(":userid", $userid, PDO::PARAM_INT);
	$log->execute();

	$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->bindParam(":u", $amount, PDO::PARAM_INT);
	$check->execute();
}

function removeCurrency($amount, $info="")
{
	$localuser = $GLOBALS['user']->id;
	$playercurrency = $GLOBALS['user']->currency;
	
	if ($playercurrency >= $amount) //if player currency is greater than or equal to the amount to remove
	{
		//log the transaction
		$log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
		$log->bindParam(":info", $info, PDO::PARAM_STR);
		$log->bindParam(":amount", $amount, PDO::PARAM_INT);
		$log->bindParam(":userid", $localuser, PDO::PARAM_INT);
		$log->execute();

		//remove amount from user
		$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency - :u) WHERE id = :i");
		$check->bindParam(":i", $localuser, PDO::PARAM_INT);
		$check->bindParam(":u", $amount, PDO::PARAM_INT);
		$check->execute();
		return true;
	}
	return false;
}

function giveItem($uid, $id)
{
	//give the user the item
	$setitem = $GLOBALS['pdo']->prepare("INSERT INTO owned_assets (uid, aid, when_sold, givenby) VALUES (:d, :a, UNIX_TIMESTAMP(), :b)");
	$setitem->bindParam(":d", $uid, PDO::PARAM_INT);
	$setitem->bindParam(":a", $id, PDO::PARAM_INT);
	$setitem->bindParam(":b", $GLOBALS['user']->id, PDO::PARAM_INT);
	if ($setitem->execute())
	{
		return true;
	}
	// ...
	return false;
}

function buyItem($id) //0 = not enough currency, 1 = already owned, 2 = bought, 3 = error
{
	$localuser = $GLOBALS['user']->id;
	$playercurrency = $GLOBALS['user']->currency;
	
	$iteminfo = getAssetInfo($id);
	$itemprice = $iteminfo->PriceInAlphabux;
	$itemcreator = $iteminfo->CreatorId;
	$onsale = $iteminfo->IsForSale;
	
	if (!isAssetModerated($id))
	{
		if ($onsale == 1) //if asset is onsale
		{
			if ($playercurrency >= $itemprice) //if the player has greater or equal amount of currency required
			{
				if (User::OwnsAsset($localuser, $id)) //if player owns the asset
				{
					return 1; //already owned
				}
				else //everything passed, do the do
				{
					$tax = 0.30; //tax percentage
					$taxtoremove = 0;
					if ($itemcreator != 1) //we dont want to tax the account Alphaland items
					{
						$taxtoremove = $tax * $itemprice;
					}

					removeCurrency($itemprice, "Purchase of asset ".$id);
					
					//give creator of the item the currency, remove tax depending on the item
					$itemprice = $itemprice - $taxtoremove; //remove tax (if any)
					
					$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
					$check->bindParam(":i", $itemcreator, PDO::PARAM_INT);
					$check->bindParam(":u", $itemprice, PDO::PARAM_INT);
					$check->execute();
					// ...
					
					//give the user the item
					$setitem = $GLOBALS['pdo']->prepare("INSERT INTO owned_assets (uid, aid, when_sold, givenby) VALUES (:d, :a, UNIX_TIMESTAMP(), :b)");
					$setitem->bindParam(":d", $localuser, PDO::PARAM_INT);
					$setitem->bindParam(":a", $id, PDO::PARAM_INT);
					$setitem->bindParam(":b", $itemcreator, PDO::PARAM_INT);
					$setitem->execute();
					// ...
					
					//sales + 1
					$sales = $GLOBALS['pdo']->prepare("UPDATE assets SET Sales = (Sales + 1) WHERE id = :i");
					$sales->bindParam(":i", $id, PDO::PARAM_INT);
					$sales->execute();
					// ...
					
					return 2; //bought
				}
			}
			else
			{
				return 0; //not enough currency
			}
		}
	}
	return 3;
}

function isOwner($id, $userid=NULL)
{
	if ($userid === NULL){
		$userid = $GLOBALS['user']->id;
	}

	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		$check = $check->fetch(PDO::FETCH_OBJ);
		$assettypeid = $check->AssetTypeId;
		$creatorid = $check->CreatorId;

		//handle game assets
		if ($assettypeid == 9) {
			if ($creatorid == $userid || $GLOBALS['user']->IsOwner()) {
				return true;
			}
			return false;
		}
		
		//others
		if ($creatorid == $userid || $GLOBALS['user']->IsStaff()) {
			return true;
		}
	}
	return false;
}

//end player appearance/inventory portion }

//friends portion {
	
function getFriends($uid) 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u OR sid = :u2) AND valid = 1 ORDER BY id DESC");
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->bindParam(":u2", $uid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function getFriendRequests()
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u) AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->execute();
		return $check;
	}
}

function friendsWithUser($user1, $user2) //for external requests not relying on cookies
{ 
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
	$check->bindParam(":u", $user1, PDO::PARAM_INT);
	$check->bindParam(":u2", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua2", $user1, PDO::PARAM_INT);
	$check->execute();
	
	if($check->rowCount() > 0) 
	{
		return true;
	}
}

function areUsersFriends($user1, $user2)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
	$check->bindParam(":u", $user1, PDO::PARAM_INT);
	$check->bindParam(":u2", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua2", $user1, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		return true;
	}
	return false;
}

function friendsWith($user)
{ 
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->bindParam(":u2", $user, PDO::PARAM_INT);
		$check->bindParam(":ua", $user, PDO::PARAM_INT);
		$check->bindParam(":ua2", $localuser, PDO::PARAM_INT);
		$check->execute();
		if($check->rowCount() > 0) 
		{
			return true;
		}
	}
	return false;
}

function friendsPending($user) //checks if the logged in user has a friend request sent to the user
{ 
	if(isLoggedIn())
	{
		if (!friendsWith($user))
		{
			$localuser = $GLOBALS['user']->id;
			
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) AND valid = 1");
			$check->bindParam(":u", $user, PDO::PARAM_INT);
			$check->bindParam(":u2", $localuser, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				return true;
			}
		}
	}
	return false;
}

function userSentFriendReq($user) //checks if the logged in user has a friend request incoming from the user (this function is mostly for security)
{ 
	if(isLoggedIn())
	{
		if (!friendsWith($user))
		{
			$localuser = $GLOBALS['user']->id;
			
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) AND valid = 1");
			$check->bindParam(":u", $localuser, PDO::PARAM_INT);
			$check->bindParam(":u2", $user, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				return true;
			}
		}
	}
	return false;
}

function invalidateFriendRequest($user) // return 0 = error, 1 = success
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
		$delfriendrequest->bindParam(":u", $localuser, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":u2", $user, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":ua", $user, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":ua2", $localuser, PDO::PARAM_INT);
		$delfriendrequest->execute();
			
		if($delfriendrequest->execute())
		{
			return 1; //success
		}
		
		/*
		if (!userSentFriendReq($user))
		{
			return 0; //invalid
		}
		else
		{
			$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
			$delfriendrequest->bindParam(":u", $localuser, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":u2", $user, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":ua", $user, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			$delfriendrequest->execute();
			
			if($delfriendrequest->execute())
			{
				return 1; //success
			}
		}
		*/
	}
	return 0; //error
}

function acceptFriendRequest($user) // return 0 = error, 1 = accepted, 2 = no friend request/invalid, 3 = already friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;

		if ($user == $localuser)
		{
			return 0;
		}
		elseif (friendsWith($user))
		{
			removeFriend($user);
			return 3; //already friends
		}
		elseif (!userSentFriendReq($user))
		{
			return 2; //no friend request/invalid
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("INSERT into friends(rid, sid, valid, whenAccepted) VALUES(:u, :u2, 1, UNIX_TIMESTAMP())");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			if($send->execute())
			{
				invalidateFriendRequest($user);
				return 1; //accepted
			}
		}
	}
	return 0; //error
}

function CreateFriend($firstuserid, $seconduserid) //used on the game server
{
	if (!areUsersFriends($firstuserid, $seconduserid)) //gotta not be friends
	{
		if (userExists($firstuserid) && userExists($seconduserid))
		{
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) OR (rid = :u4 and sid = :u3) AND valid = 1");
			$check->bindParam(":u", $firstuserid, PDO::PARAM_INT);
			$check->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
			$check->bindParam(":u3", $firstuserid, PDO::PARAM_INT);
			$check->bindParam(":u4", $seconduserid, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
				$delfriendrequest->bindParam(":u", $firstuserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":ua", $seconduserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":ua2", $firstuserid, PDO::PARAM_INT);
				$delfriendrequest->execute();
			}
			
			$newfriend = $GLOBALS['pdo']->prepare("INSERT into friends(rid, sid, valid, whenAccepted) VALUES(:u, :u2, 1, UNIX_TIMESTAMP())");
			$newfriend->bindParam(":u", $firstuserid, PDO::PARAM_INT);
			$newfriend->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
			$newfriend->execute();
		}
	}
}

function BreakFriend($firstuserid, $seconduserid) //used on the game server
{
	$remove = $GLOBALS['pdo']->prepare("DELETE FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
	$remove->bindParam(":u", $firstuserid, PDO::PARAM_INT);
	$remove->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
	$remove->bindParam(":ua", $seconduserid, PDO::PARAM_INT);
	$remove->bindParam(":ua2", $firstuserid, PDO::PARAM_INT);
	$remove->execute();
}

function sendFriendRequest($user) // return 0 = error, 1 = sent, 2 = already pending, 3 = already friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;

		if ($user == $localuser)
		{
			return 0;
		}
		elseif (friendsWith($user))
		{
			return 3; //already friends
		}
		elseif (friendsPending($user))
		{
			return 2; //already sent
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("INSERT into friend_requests(rid, sid, whenSent, valid) VALUES(:u2, :u, UNIX_TIMESTAMP(), 1)");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //sent
			}
		}
	}
	return 0; //error
}

function removeFriend($user) //return 0 = error, 1 = removed friend, 2 = not friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		if ($user == $localuser)
		{
			return 0;
		}
		elseif (!friendsWith($user))
		{
			return 2; //not friends
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("DELETE FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			$send->bindParam(":ua", $user, PDO::PARAM_INT);
			$send->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //removed friend
			}
			
			/*
			$send = $GLOBALS['pdo']->prepare("UPDATE friends SET valid = 0 WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			$send->bindParam(":ua", $user, PDO::PARAM_INT);
			$send->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //removed friend
			}
			*/
		}
	}
	return 0; //error
}

//end friends portion }

//shouts {

function shoutCooldown()
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_shouts WHERE uid = :u ORDER BY whenShouted DESC LIMIT 1");
	$check->bindParam(":u", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0) {
		$interval = (int)$check->fetch(PDO::FETCH_OBJ)->whenShouted;
		if($interval + 60 > time()) {
			return true;
		}
	}
	return false;
}

function setShout($newshout)
{
	$localplayer = $GLOBALS['user']->id;

	if (shoutCooldown()) //ratelimit
	{
		return "Please wait before posting again";
	}
	if (empty($newshout) || ctype_space($newshout)) //we no want empty shouts or just spaces
	{
		return "Invalid shout, can't be empty";
	}
	elseif (strlen($newshout)>128) //limit 128 characters
	{
		return "Invalid shout, must be below 128 Characters";
	}
	elseif (strlen($newshout)<5) //must be over 5 characters
	{
		return "Invalid shout, must be above 5 Characters";
	}
	else
	{
		$data = ['shout' => cleanInput($newshout), 'uid' => $localplayer];
		$shout = $GLOBALS['pdo']->prepare("INSERT into user_shouts (shout, uid, whenShouted) VALUES(:shout, :uid, UNIX_TIMESTAMP())");
		if ($shout->execute($data))
		{
			return true;
		}
	}
	return "Error Occurred";
}

function userShout($uid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM `user_shouts` WHERE uid = :u ORDER BY whenShouted DESC LIMIT 1");
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		$check = $check->fetch(PDO::FETCH_OBJ);
		
		if (!empty($check->shout) or !ctype_space($check->shout)) //detect empty shout or shout with just spaces
		{
			return cleanOutput($check->shout);
		}
	}
	return "";
}
	
//end of shouts }

//games portion {
	
function createPlace($userid, $name, $description, $maxplayers)
{
	//file locations
	$defaultplacedir = $GLOBALS['defaultPlacesPath'] . 'default.rbxl';
	$assetcdn = $GLOBALS['assetCDNPath'];
						
	//grab a new hash for the game asset
	$gamehash = genAssetHash(16);
	
	//copy the default place to the assets cdn
	copy($defaultplacedir, $assetcdn . $gamehash);
	
	//one of the random thumbs
	$thumb = rand(4, 6); 
	
	return CreateAsset(
		9, //AssetTypeId
		$thumb, //IconImageAssetId
		NULL, //TargetId(not used atm)
		NULL, //ProductType(idk what to do with this atm)
		cleanInput($name), //Name
		cleanInput($description), //Description
		time(), //Created
		time(), //Updated
		$userid, //CreatorId
		0, //PriceInAlphabux
		0, //Sales
		false, //isPersonalServer
		false, //IsNew
		false, //IsForSale
		false, //IsPublicDomain
		false, //IsLimited
		false, //IsLimitedUnique
		true, //IsCommentsEnabled
		true, //IsApproved
		false, //IsModerated
		0, //Remaining
		0, //MinimumMembershipLevel
		0, //ContentRatingTypeId
		0, //Favorited
		0, //Visited
		$maxplayers, //MaxPlayers
		0, //UpVotes
		0, //DownVotes
		$gamehash, //Hash
		NULL //ThumbHash
	);
}

function createPBSPlace($userid, $name, $description, $maxplayers, $path)
{
	//file locations
	$assetcdn = $GLOBALS['assetCDNPath'];
						
	//grab a new hash for the game asset
	$gamehash = genAssetHash(16);
	
	//copy the default place to the assets cdn
	copy($path, $assetcdn . $gamehash);
	
	//one of the random thumbs (until rendered)
	$thumb = rand(4, 6); 
	
	$newpbs = CreateAsset(
		9, //AssetTypeId
		$thumb, //IconImageAssetId
		NULL, //TargetId(not used atm)
		NULL, //ProductType(idk what to do with this atm)
		cleanInput($name), //Name
		cleanInput($description), //Description
		time(), //Created
		time(), //Updated
		$userid, //CreatorId
		0, //PriceInAlphabux
		0, //Sales
		true, //isPersonalServer
		false, //IsNew
		false, //IsForSale
		false, //IsPublicDomain
		false, //IsLimited
		false, //IsLimitedUnique
		true, //IsCommentsEnabled
		true, //IsApproved
		false, //IsModerated
		0, //Remaining
		0, //MinimumMembershipLevel
		0, //ContentRatingTypeId
		0, //Favorited
		0, //Visited
		$maxplayers, //MaxPlayers
		0, //UpVotes
		0, //DownVotes
		$gamehash, //Hash
		NULL //ThumbHash
	);
	handleRenderPlace($newpbs);
	return $newpbs;
}
	
function getAllGames($uid) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE AssetTypeId = 9 AND CreatorId = :i");
	$check->bindParam(":i", $uid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function userPlaceVisits($userid)
{
	$games = $GLOBALS['pdo']->prepare("SELECT SUM(Visited) FROM `assets` WHERE `AssetTypeId` = 9 AND `CreatorId` = :userid");
	$games->bindParam(":userid", $userid, PDO::PARAM_INT);
	$games->execute();
	return $games->fetch(PDO::FETCH_NUM)[0];
	
}

//end games portion }

//utility {
	
function enableMaintenance($custom)
{
	if (!empty($custom)) {
		$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 1, maintenance_text = :t");
		$setmaintenance->bindParam(":t", $custom, PDO::PARAM_STR);
		$setmaintenance->execute();
	} else {
		$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 1");
		$setmaintenance->execute();
	}

	soapCloseAllJobs($GLOBALS['gamesArbiter']);
}

function disableMaintenance()
{
	$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 0, maintenance_text = ''");
	$setmaintenance->execute();
}

function setUserRank($rank, $userid)
{
	$updaterank = $GLOBALS['pdo']->prepare("UPDATE users SET rank = :r WHERE id = :i");
	$updaterank->bindParam(":r", $rank, PDO::PARAM_INT);
	$updaterank->bindParam(":i", $userid, PDO::PARAM_INT);
	$updaterank->execute();
}

function onlineUsersCount() 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users"); 
	$check->execute();
	
	$count = 0;
	foreach ($check as $user)
	{
		$info = userInfo($user['id']); // add true as a second param if u wanna use usernames instead
		if (($info->lastseen + 120) > time())
		{
			$count = $count + 1;
		}
	}
	return $count;
}

function getUsername($id) {
	$get = $GLOBALS['pdo']->prepare("SELECT username FROM users WHERE id = :u");
	$get->bindParam(":u", $id, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id->username;
	}
	return false; //user not found
}

function userExists($id)
{
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$get->bindParam(":i", $id, PDO::PARAM_INT);
	$get->execute();
	if($get->rowCount() > 0) 
	{
		return true;
	}
	return false;
}

function userInfo($id, $useID = true) {
	if($useID) {
		$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u");
	} else {
		$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE username = :u");
	}
	$get->bindParam(":u", $id, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id;
	}
	return false; //user not found
}

function isLoggedIn()
{
	if($GLOBALS['user']->logged_in)
	{
		return true;
	}
	return false;
}

function getID($username) {
	$get = $GLOBALS['pdo']->prepare("SELECT id FROM users WHERE username = :u");
	$get->bindParam(":u", $username, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id->id;
	}
	return false; //user not found
}

function isValidPasswordResetToken($token)
{
	$passreset = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :token");
	$passreset->bindParam(":token", $token, PDO::PARAM_STR);
	$passreset->execute();
	if ($passreset->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function pageHandler() {
	require 'pageHandler.php';
	$GLOBALS['ph'] = new page_handler();
}

function usernameExists($u) {
	$check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
	$check->bindParam(":u", $u, PDO::PARAM_STR);
	$check->execute();
	if($check->fetchColumn(0) > 0) {
		return true;
	}
	return false;
}

function cleanInput($t) {
	//$t = trim(preg_replace('/\s+/', ' ', $t)); //new line
	return trim((string)$t);
}

function cleanOutput($t, $linebreaks=true) {
	$t = htmlentities($t);
	if ($linebreaks) {
		$t = nl2br($t);
	}
	$t = strip_tags($t, '<br>');
	return Filter::FilterText($t);
}

function cleanOutputNoFilter($t, $linebreaks=true) {
	$t = htmlentities($t);
	if ($linebreaks) {
		$t = nl2br($t);
	}
	return strip_tags($t, '<br>');
}

//theme stuff
function setTheme($theme) //sets the users theme
{
	$localplayer = $GLOBALS['user']->id;
	$numberofthemes = 1;

	if ($theme > $numberofthemes)
	{
		return false;
	}
	else
	{
		$check = $GLOBALS['pdo']->prepare("UPDATE users SET theme = :t WHERE id = :i");
		$check->bindParam(":t", $theme, PDO::PARAM_INT);
		$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
		if ($check->execute())
		{
			return true;
		}
	}
}

function getCurrentTheme() //returns the theme set (integer)
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		$currenttheme = (int)$check->fetch(PDO::FETCH_OBJ)->theme;
		
		return $currenttheme;
	}
}

function getCurrentThemeLogo() //grabs the alphaland logo for the users selected theme
{
	$currenttheme = getCurrentTheme();

	//return $GLOBALS['url'] . "/alphaland/cdn/imgs/alpha-christmas/alphalandchristmas.png"; //force christmas logo
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphaland-logo.svg";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphaland-white-1024.png";
	}
}

function getCurrentThemeAlphabuxLogo() //grabs the alphaland alphabux logo for the users selected theme
{
	$currenttheme = getCurrentTheme();
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphabux-black-1024.png";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphabux-grey-1024.png";
	}
}

function getCurrentThemeAnimatedLogo() //grabs the alphaland animated logo for the users selected theme
{
	$currenttheme = getCurrentTheme();
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/loading-dark.gif";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/loading-light.gif";
	}
}

function getCurrentThemeStyle() //grabs the style sheet for the users selected theme
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		$currenttheme = (int)$check->fetch(PDO::FETCH_OBJ)->theme;
		
		if ($currenttheme == 1) //dark theme
		{
			return '<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/stylesheet-dark.css?version='.$GLOBALS['cssversion'].'">';
		}
	}
}

function getCSS($studio=false) 
{
	$theme = "";
	if (!$studio)
	{
		$theme = getCurrentThemeStyle($studio);
	}

	return '
		<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/stylesheet.css?version='.$GLOBALS['cssversion'].'">
		<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/bootstrap.min.css?version='.$GLOBALS['cssversion'].'">
		'.$theme.'
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
		<script src="https://use.fontawesome.com/releases/v5.10.0/js/all.js" data-search-pseudo-elements></script>
		<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://www.alphaland.cc/alphaland/js/bootstrap.min.js?version='.$GLOBALS['jsversion'].'"></script>
		<script type="text/javascript" src="https://www.alphaland.cc/alphaland/js/utilities.js?version='.$GLOBALS['jsversion'].'"></script>';
		
}
//end theme stuff

//utilities

function getFooter() 
{
	$usercount = (int)onlineUsersCount();
	$year = date("Y");
	
	return '<div class="container mt-3">
	<div class="footer">
		<div class="container">
			<div class="row">
				<div class="col">
					<div class="container p-3">
						<div class="row">
							<div class="col-sm">
								<strong>Alphaland</strong> | <a style="color:grey;">'.$year.'</a>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<a style="color:green;"><i class="fas fa-globe"></i> '.$usercount.'</a>
							</div>
						</div>
						<div class="row border-bottom pb-1">
							<div class="col-sm">
								<a>Alphaland is not affiliated with Lego, ROBLOX, MegaBloks, Bionicle, Pokemon, Nintendo, Lincoln Logs, Yu Gi Oh, K\'nex, Tinkertoys, Erector Set, or the Pirates of the Caribbean. ARrrr!</a>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<a style="font-size: 1.6rem;color:red;" href="https://www.youtube.com/channel/UC5o1iJC9wonCWPvTvtORklg"><i class="fab fa-youtube"></i></a>
								<a style="font-size: 1.6rem;color:#1DA1F2;" href="https://twitter.com/_Alphaland"><i class="fab fa-twitter"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';
}

function getNav() 
{
	$logo = getCurrentThemeLogo();
	$friendreqcount = getFriendRequests();
	$announcement = fetchAnnouncement();
	$thumbnailerstatus = "";
	$gameserverstatus = "";

	$modbutton = "";
	$pendingassetscount = 0;
	if ($GLOBALS['user']->IsStaff())
	{
		$pendingassetscount = fetchPendingAssets()->rowCount();

		$modbuttonbadge = "";
		if ($pendingassetscount > 0)
		{
			$modbuttonbadge = '
				<span class="badge badge-danger">'.$pendingassetscount.'</span>
			';
		}

		$modbutton = '
		<li class="nav-item">
			<a class="nav-link" style="font-size: 1rem;" href="/MCP/">
				Mod
				'.$modbuttonbadge.'
			</a>
		</li>
		';
	}

	if (WebContextManager::IsUnderMaintenance(true))
	{
		$maintenancestatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>MAINTENANCE MODE IS ENABLED</div>";
	}
	
	if(!isThumbnailerAlive())
	{
		$thumbnailerstatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>WARNING: Thumbnailer is offline, no Avatar changes will be applied</div>";
	}
	
	if (!Game::ArbiterOnline())
	{
		$gameserverstatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>WARNING: Gameserver is offline, games will not launch</div>";
	}
	
	if(isLoggedIn())
	{
		$friendreqbadge = '';
		if ($friendreqcount->rowCount() > 0)
		{
			$friendreqbadge = '<span class="badge badge-danger"> '.$friendreqcount->rowCount().'';
		}
		
		$user = $GLOBALS['user'];
		return '
			<header>
				<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-right:10%;padding-left:10%;">
					<a class="navbar-brand" href="/">
						<img src="'.$logo.'" width="40" height="40" class="d-inline-block align-top" alt="" loading="lazy">
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarNav">
						<ul class="navbar-nav">
							<li class="nav-item">
								<a class="nav-link" href="/">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/profile/">Profile</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/avatar">Avatar</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/catalog/">Catalog</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/games/">Games</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/users/">Users</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/groups/">Groups</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/create/">Create</a>
							</li>
							'.$modbutton.'
							'.(($user->IsAdmin())? '
							<li class="nav-item">
								<a class="nav-link" href="https://crackpot.alphaland.cc">Admin</a>
							</li>':'').'
						</ul>
						<ul class="navbar-nav ml-auto">
							<li class="nav-item">
								<a class="nav-link mr-3" style="font-size: 1rem;" href="/friends/friend-requests">
									<i class="fas fa-user-friends"></i>
										'.$friendreqbadge.'
									</span>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link mr-3" href=""><img style="width:1rem;" src="/alphaland/cdn/imgs/alphabux-grey-1024.png"> '.$user->currency.'</a>
							</li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$user->name.'</a>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
									<a class="dropdown-item" href="/settings/">Settings</a>
									<div class="dropdown-divider"></div>
									<a class="dropdown-item" href="/logout">Log Out</a>
								</div>	
							</li>
						</ul>
					</div>
				</nav>
				'.$maintenancestatus.'
				'.$announcement.'
				'.$thumbnailerstatus.'
				'.$gameserverstatus.'
			</header>
			<script>
				setInterval(function(){ getJSONCDS("https://api.alphaland.cc/sitepresence/ping"); }, 60000); //ping every minute;
			</script>
			<br/>';
	}
	return '
		<header>
			<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-right:10%;padding-left:10%;">
				<a class="navbar-brand" href="/">
					<img src="/alphaland/cdn/imgs/alphaland-logo.svg" width="40" height="40" class="d-inline-block align-top" alt="" loading="lazy">
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" href="/">Home</a>
						</li>
					</ul>
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a href="/login"><button class="btn btn-danger">Log In</button></a>
					</li>
				</ul>
				</div>
			</nav>
		</header><br />';
}

function fetchAnnouncement() 
{
	$announcementquery = $GLOBALS['pdo']->prepare("SELECT * FROM websettings");
	$announcementquery->execute();
	$announcementquery = $announcementquery->fetch(PDO::FETCH_OBJ);
	$announcement = cleanOutput($announcementquery->announcement); //clean output
	if (empty($announcementquery->announcement))
	{
		return "";
	}
	else
	{
		$html = "";
		if ($announcementquery->announcement_color == "red")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>{$announcement}</div>";
		}
		elseif ($announcementquery->announcement_color == "blue")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-primary' role='alert'>{$announcement}</div>";
		}
		elseif ($announcementquery->announcement_color == "green")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-success' role='alert'>{$announcement}</div>";
		}
		return $html;
	}
}

function canRegister()
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM websettings WHERE registration = 1");
	$check->execute();
	
	if($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function adminPanelStats() {
	$maintenancestatus = "ON";
	if (!WebContextManager::IsUnderMaintenance(true))
	{
		$maintenancestatus = "OFF";
	}

	$thumbnailerstatus = "OK";
	if(!isThumbnailerAlive())
	{
		$thumbnailerstatus = "DOWN";
	}

	$gameserverstatus = "OK";
	if (!Game::ArbiterOnline())
	{
		$gameserverstatus = "DOWN";
	}

	$faults = $GLOBALS['pdo']->prepare("SELECT * FROM soap_faults");
	$faults->execute();
	$faults = $faults->rowCount();

	echo '
	<div>Account: '.getUsername($GLOBALS["user"]->id).'</div>
	<div>Server Date (EST): '.date("m/d/Y", time()).'</div>
	<div>Server Time (EST): '.shell_exec('TIME /T').'</div>
	<div>Server OS Version: Microsoft Windows Server 2012 R2 Standard</div>
	<div>NGINX Version: '.$_SERVER['SERVER_SOFTWARE'].'</div>
	<div>PHP Version: '.phpversion().'</div>
	<div>MySQL Version: '.shell_exec('mysql --version').'</div>
	<div>SOAP Faults: '.$faults.'</div>
	<div>Maintenance Status:  '.$maintenancestatus.'</div>
	<div>Gameserver Status: '.$gameserverstatus.'</div>
	<div>Thumbnail Server Status: '.$thumbnailerstatus.'</div>
	<hr>
	';
}

//end utility }
