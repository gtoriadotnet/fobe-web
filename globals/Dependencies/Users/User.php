<?php

namespace Finobe\Users {

    use Finobe\Assets\Asset;
    use Exception;
    use PDO;

    class User
    {
        public static function UserExists(int $userid)
        {
            $get = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM users WHERE id = :i");
            $get->bindParam(":i", $userid, PDO::PARAM_INT);
            $get->execute();
            if($get->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function GetUserInfo(int $userid) 
        {
            $user = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u");
            $user->bindParam(":u", $userid, PDO::PARAM_STR);
            $user->execute();
            if($user->rowCount() > 0) {
                return $user->fetch(PDO::FETCH_OBJ);
            }
        }

        public static function ValidatePassword(int $userid, string $password) 
        {
            $userpassword = $GLOBALS['pdo']->prepare("SELECT pwd FROM users WHERE id = :i");
            $userpassword->bindParam(":i", $userid, PDO::PARAM_INT);
            $userpassword->execute();
            if($userpassword->rowCount() > 0) {
                if(password_verify($password, $userpassword->fetch(PDO::FETCH_OBJ)->pwd)) {
                    return true; //correct
                }
            }
            return false;
        }

        public static function SetHeadshotAngleRight(int $userid)
        {
            $right = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 1, headshotAngleLeft = 0 WHERE id = :uid');
            $right->bindParam(":uid", $userid, PDO::PARAM_INT);
            $right->execute();
            if ($right->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function SetHeadshotAngleLeft(int $userid)
        {
            $left = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 0, headshotAngleLeft = 1 WHERE id = :uid');
            $left->bindParam(":uid", $userid, PDO::PARAM_INT);
            $left->execute();
            if ($left->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function SetHeadshotAngleCenter(int $userid)
        {
            $center = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 0, headshotAngleLeft = 0 WHERE id = :uid');
            $center->bindParam(":uid", $userid, PDO::PARAM_INT);
            $center->execute();
            if ($center->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function GetWearingAssetsString(int $userid) //returns wearing asset list separated by ;
        {
            $wearingitems = $GLOBALS['pdo']->prepare('SELECT * FROM wearing_items WHERE uid = :uid ORDER BY aid ASC'); //wearing items from lowest to highest (EZ)
            $wearingitems->bindParam(":uid", $userid, PDO::PARAM_INT);
            $wearingitems->execute();
            
            $iter = 0;
            $wearingassets = "";
            foreach($wearingitems as $item) {
                $iter += 1;
                $wearingassets .= ($iter == $wearingitems->rowCount()) ? $item['aid'] : $item['aid'] . ';';
            }
            return $wearingassets;
        }

        public static function WearingItemsCount(int $userid, int $assettype)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `wearing_items` WHERE `uid` = :userid AND (SELECT COUNT(*) from `assets` WHERE `id` = wearing_items.aid AND `AssetTypeId` = :assettypeid) > 0"); 
            $check->bindParam(":userid", $userid, PDO::PARAM_INT);
            $check->bindParam(":assettypeid", $assettype, PDO::PARAM_INT);
            $check->execute();
            return $check->fetchColumn();
        }

        public static function LastWornItem(int $userid, int $assettype)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT aid FROM `wearing_items` WHERE `uid` = :userid AND (SELECT COUNT(*) from `assets` WHERE `id` = wearing_items.aid AND `AssetTypeId` = :assettypeid) > 0 ORDER BY whenWorn DESC LIMIT 1"); 
            $check->bindParam(":userid", $userid, PDO::PARAM_INT);
            $check->bindParam(":assettypeid", $assettype, PDO::PARAM_INT);
            $check->execute();
            return $check->fetchColumn();
        }

        public static function SetCanJoinUser(int $userid, int $status)
        {
            if ($status <= 2) {
                $setstatus = $GLOBALS['pdo']->prepare("UPDATE users SET canJoin = :c WHERE id = :u");
                $setstatus->bindParam(":c", $status, PDO::PARAM_INT);
                $setstatus->bindParam(":u", $userid, PDO::PARAM_INT);
                $setstatus->execute();
                if ($setstatus->rowCount() > 0) {
                    return true; 
                }
            }
            return false;
        }

        public static function CanJoinUser(int $targetuser) //TODO: fix when friends class is implemented
        {
            /*
                0 = no one
                1 = friends
                2 = everyone
            */

            $canjoin = User::GetUserInfo($targetuser)->canJoin;
            if($canjoin == 1) {
                if (friendsWith($targetuser)) {
                    return true;
                }
            } else if ($canjoin == 2) {
                return true;
            }
            return false;
        }

        public static function SiteStatus(int $userid)
        {
            $p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
            $p->bindParam(":i", $userid, PDO::PARAM_INT);
            $p->execute();
            $userinfo = User::GetUserInfo($userid);
                            
            if($p->rowCount() > 0) {    
                if (User::CanJoinUser($userinfo->id)) {
                    return cleanOutput(Asset::GetAssetInfo($p->fetch(PDO::FETCH_OBJ)->placeid)->Name);
                } else {
                    return 'In-Game';
                }			
            }
            else {
                if (($userinfo->lastseen + 120) > time()) {
                    return 'Online';
                } else {
                    return 'Offline';
                }
            }
        }

        public static function UserPlaying(int $userid)
        {
            $p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
            $p->bindParam(":i", $userid, PDO::PARAM_INT);
            $p->execute();
                            
            if($p->rowCount() > 0) {
                if (User::CanJoinUser($userid)) {
                    $playingInfo = $p->fetch(PDO::FETCH_OBJ);
                    return array (
                        "placeid" => $playingInfo->placeid,
                        "jobid" =>  $playingInfo->jobid
                    );
                }
            }		
            return array (
                "placeid" => null,
                "jobid" =>  null
            );
        }

        public static function SetIsInventoryPrivate(int $userid, int $status)
        {
            if ($status <= 2) {
                $setstatus = $GLOBALS['pdo']->prepare("UPDATE users SET privateInventory = :c WHERE id = :u");
                $setstatus->bindParam(":c", $status, PDO::PARAM_INT);
                $setstatus->bindParam(":u", $userid, PDO::PARAM_INT);
                $setstatus->execute();
                if ($setstatus->rowCount() > 0) {
                    return true; 
                }
            }
            return false;
        }

        public static function IsInventoryPrivate(int $targetuser)
        {
            /*
                0 = no one
                1 = friends
                2 = everyone
            */

            $inventoryView = User::GetUserInfo($targetuser)->privateInventory;
            if ($targetuser == $GLOBALS['user']->id) {
                return false;
            } else if ($inventoryView == 1 && friendsWith($targetuser)) {
                return false;
            } else if ($inventoryView == 2) {
                return false;
            }
            return true;
        }

        public static function OwnsAsset(int $userid, $assetid)
        {
            $ownership = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `owned_assets` WHERE `aid` = :assetid AND `uid` = :userid");
            $ownership->bindParam(":assetid", $assetid, PDO::PARAM_INT);
            $ownership->bindParam(":userid", $userid, PDO::PARAM_INT);
            $ownership->execute();
            if($ownership->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function IsWearingItem($userid, $assetid)
        {
            $wearing = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM wearing_items WHERE uid = :userid AND aid = :assetid");
            $wearing->bindParam(":userid", $userid, PDO::PARAM_INT);
            $wearing->bindParam(":assetid", $assetid, PDO::PARAM_INT);
            $wearing->execute();
            if ($wearing->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function DeequipAsset(int $userid, int $assetid, bool $force=false)
        {
            if (!User::IsWearingItem($userid, $assetid) && !$force) {
                throw new Exception('Error occurred');
            } else if (!isThumbnailerAlive() && !$force) {
                throw new Exception('Thumbnail Server offline');
            } else if (Render::RenderCooldown($userid) && !$force) {
                throw new Exception('Slow down!');
            } else {
                $deequip = $GLOBALS['pdo']->prepare("DELETE from wearing_items WHERE uid = :userid AND aid = :assetid"); //delete db key
                $deequip->bindParam(":userid", $userid, PDO::PARAM_INT);
                $deequip->bindParam(":assetid", $assetid, PDO::PARAM_INT);
                $deequip->execute();
                if (!$force) {
                    Render::RenderPlayer($userid);
                }
                return true;
            }
        }

        public static function EquipAsset(int $userid, int $assetid, bool $force=false)
        {
            $asset = Asset::GetAssetInfo($assetid);
            if (!$asset || !User::OwnsAsset($userid, $assetid) || !isWearable($asset->AssetTypeId) && !$force) {
                throw new Exception('Error occurred');
            } else if (User::IsWearingItem($userid, $assetid) && !$force) {
                throw new Exception('Already wearing this item');
            } else if (!isThumbnailerAlive() && !$force) {
                throw new Exception('Thumbnail Server offline');
            } else if (Render::RenderCooldown($userid) && !$force) {
                throw new Exception('Slow down!');
            } else if (isAssetModerated($assetid) && !$force) {
                throw new Exception('This item is moderated');
            } else {
                if (User::WearingItemsCount($userid, $asset->AssetTypeId) >= typeToMaxCosmetic($asset->AssetTypeId) && !$force) {
                    User::DeequipAsset($userid, User::LastWornItem($userid, $asset->AssetTypeId), true);
                }
                $equip = $GLOBALS['pdo']->prepare("INSERT INTO wearing_items(uid,aid,whenWorn) VALUES(:userid,:assetid,UNIX_TIMESTAMP())");
                $equip->bindParam(":userid", $userid, PDO::PARAM_INT);
                $equip->bindParam(":assetid", $assetid, PDO::PARAM_INT);
                $equip->execute();
                if (!$force) {
                    Render::RenderPlayer($userid);
                }
                return true;
            }         
        }
    }
}
