<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Assets {

use Alphaland\Common\HashingUtiltity;
use PDO;

class Asset
    {
        private static function GenerateHash(int $len)
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len);      
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT COUNT (*) FROM assets WHERE Hash = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
            } while ($tokencheck->fetchColumn() != 0);
            return $hash;
        }

        public static function AvailableId() 
        {
            $GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
            $b = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM assets");
            $b->execute();
            $GLOBALS['pdo']->exec("UNLOCK TABLES");
            return $b->fetchColumn() + 1;
        }

        public static function CreateBasicAsset(int $assetid, int $assettypeid, int $targetid, string $producttype, string $name, string $description, int $creatorid, int $price, bool $onsale, bool $ispublicdomain, bool $isapproved, string $hash) 
        {
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
            $asset->bindParam(":PriceInAlphabux", $price, PDO::PARAM_INT);
            $asset->bindParam(":IsForSale", $onsale, PDO::PARAM_INT);
            $asset->bindParam(":Hash", $hash, PDO::PARAM_STR);
            $asset->execute();
        
            $GLOBALS['pdo']->exec("UNLOCK TABLES");
        }

        public static function CreateAsset(int $id, int $AssetTypeId, int $IconImageAssetId, int $TargetId, string $ProductType, string $Name, string $Description, $Created, $Updated, $CreatorId, $PriceInAlphabux, $Sales, $isPersonalServer, $IsNew, $IsForSale, $IsPublicDomain, $IsLimited, $IsLimitedUnique, $IsCommentsEnabled, $IsApproved, $IsModerated, $Remaining, $MinimumMembershipLevel, $ContentRatingTypeId, $Favorited, $Visited, $MaxPlayers, $UpVotes, $DownVotes, $Hash, $ThumbHash)
        {
            //setup the new asset in the DB, lock it!
            $GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
            
            //db entry
            $m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(Id, AssetTypeId, IconImageAssetId, TargetId, ProductType, Name, Description, Created, Updated, CreatorId, PriceInAlphabux, Sales, isPersonalServer, IsNew, IsForSale, IsPublicDomain, IsLimited, IsLimitedUnique, IsCommentsEnabled, IsApproved, IsModerated, Remaining, MinimumMembershipLevel, ContentRatingTypeId, Favorited, Visited, MaxPlayers, UpVotes, DownVotes,Hash,ThumbHash) VALUES (:Id, :AssetTypeId, :IconImageAssetId, :TargetId, :ProductType, :Name, :Description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :CreatorId, :PriceInAlphabux, :Sales, :isPersonalServer, :IsNew, :IsForSale, :IsPublicDomain, :IsLimited, :IsLimitedUnique, :IsCommentsEnabled, :IsApproved, :IsModerated, :Remaining, :MinimumMembershipLevel, :ContentRatingTypeId, :Favorited, :Visited, :MaxPlayers, :UpVotes, :DownVotes, :Hash, :ThumbHash)");		
            $m->bindParam(":Id", $id, PDO::PARAM_INT);
            $m->bindParam(":AssetTypeId", $AssetTypeId, PDO::PARAM_INT);
            $m->bindParam(":IconImageAssetId", $IconImageAssetId, PDO::PARAM_INT);
            $m->bindParam(":TargetId", $TargetId, PDO::PARAM_INT);
            $m->bindParam(":ProductType", $ProductType, PDO::PARAM_STR);
            $m->bindParam(":Name", $Name, PDO::PARAM_STR);
            $m->bindParam(":Description", $Description, PDO::PARAM_STR);
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
        }

        public static function ConvertAssetUrlToId(int $asseturl) 
        {
            if (strpos($asseturl, "rbxassetid://") !== false) {
                return substr($asseturl, strpos($asseturl, "rbxassetid://")+13, strlen($asseturl));
            } else if (strpos($asseturl, "id=") !== false) {
                return substr($asseturl, strpos($asseturl, "id=")+3, strlen($asseturl));
            }
            return false;
        }
        
        public static function IsMeshSupported(string $meshstr) 
        {
            if (strpos($meshstr, "version 1.00") !== false || strpos($meshstr, "version 1.01") !== false || strpos($meshstr, "version 2.00") !== false) {
                return true;
            }
            return false;
        }

        public static function SetAssetModerated(int $id)
        {
            $moderate = $GLOBALS['pdo']->prepare("UPDATE assets SET IsModerated = 1, IsApproved = 0, IsForSale = 0 WHERE id = :i");
            $moderate->bindParam(":i", $id, PDO::PARAM_INT);
            $moderate->execute();
        }
        
        public static function SetAssetApproved(int $id)
        {
            $approve = $GLOBALS['pdo']->prepare("UPDATE assets SET IsApproved = 1, IsModerated = 0 WHERE id = :i");
            $approve->bindParam(":i", $id, PDO::PARAM_INT);
            $approve->execute();
        }

        public static function IsAssetApproved(int $id)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM assets WHERE id = :i AND IsApproved = 1");
            $check->bindParam(":i", $id, PDO::PARAM_INT);
            $check->execute();
            return $check->fetchColumn() > 0;
        }

        public static function IsModerated(int $id)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM assets WHERE id = :i AND IsModerated = 1");
            $check->bindParam(":i", $id, PDO::PARAM_INT);
            $check->execute();     
            return $check->fetchColumn() > 0;
        }

        public static function GetAssetInfo(int $id) 
        {
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
            $check->bindParam(":i", $id, PDO::PARAM_INT);
            $check->execute();
            if($check->rowCount() > 0) {
                return $check->fetch(PDO::FETCH_OBJ);
            }
            return false;
        }

        public static function AddSale(int $assetid)
        {
            $sales = $GLOBALS['pdo']->prepare("UPDATE assets SET Sales = (Sales + 1) WHERE id = :i");
            $sales->bindParam(":i", $assetid, PDO::PARAM_INT);
            $sales->execute();
            if ($sales->rowCount() > 0) {
                return true;
            }
            return false;
        }
        
        public static function GiveAsset(int $assetid, int $userid, int $givenby=1)
        {
            $setitem = $GLOBALS['pdo']->prepare("INSERT INTO owned_assets (uid, aid, when_sold, givenby) VALUES (:d, :a, UNIX_TIMESTAMP(), :b)");
		    $setitem->bindParam(":d", $userid, PDO::PARAM_INT);
		    $setitem->bindParam(":a", $assetid, PDO::PARAM_INT);
			$setitem->bindParam(":b", $givenby, PDO::PARAM_INT);
			$setitem->execute();
            if ($setitem->rowCount() > 0) {
                return true;
            }
            return false;
        }
    }
}
