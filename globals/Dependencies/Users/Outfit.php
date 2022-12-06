<?php

/*
    Fobe 2021
*/

namespace Fobe\Users {

    use Fobe\Users\User;
    use Fobe\Users\Render;
    use Exception;
    use PDO;

    class Outfit
    {
        public static function UserOutfitCount(int $userid)
        {
            $outfits = $GLOBALS['pdo']->prepare('SELECT COUNT(*) FROM `user_outfits` WHERE `userid` = :uid');
            $outfits->bindParam(":uid", $userid, PDO::PARAM_INT);
            $outfits->execute();
            return $outfits->fetchColumn();
        }

        public static function UserOwnsOutfit(int $userid, int $outfitid)
        {
            $outfit = $GLOBALS['pdo']->prepare('SELECT COUNT(*) FROM `user_outfits` WHERE `userid` = :uid AND `id` = :id');
            $outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
            $outfit->bindParam(":id", $outfitid, PDO::PARAM_INT);
            $outfit->execute();
            if ($outfit->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function ThumbHashInOutfit(string $thumbhash)
        {
            $outfit = $GLOBALS['pdo']->prepare('SELECT COUNT(*) FROM `user_outfits` WHERE `ThumbHash` = :hash');
            $outfit->bindParam(":hash", $thumbhash, PDO::PARAM_STR);
            $outfit->execute();
            if ($outfit->fetchColumn() > 0 || $thumbhash == $GLOBALS['defaultOutfitHash']) { //default outfit hash
                return true;
            }
            return false;
        }

        public static function HeadshotThumbHashInOutfit(string $thumbhash)
        {
            $outfit = $GLOBALS['pdo']->prepare('SELECT COUNT(*) FROM user_outfits WHERE HeadshotThumbHash = :hash');
            $outfit->bindParam(":hash", $thumbhash, PDO::PARAM_STR);
            $outfit->execute();
            if ($outfit->fetchColumn() > 0 || $thumbhash == $GLOBALS['defaultHeadshotHash']) { //default headshot hash
                return true;
            }
            return false;
        }

        public static function CreateOutfit(string $name, int $userid)
        {
            $name = cleanInput($name);

            if (strlen($name) <= 3) {
                throw new Exception('Name too short, must be above 3 characters 2');
            }
            else if (strlen($name) >= 50) {
                throw new Exception('Name too long, must be less than 50 characters 2');
            }
            else if (Outfit::UserOutfitCount($userid) >= 24) {
                throw new Exception('Limit of 24 outfits');
            }
            else if (Render::PendingRender($userid)) {
                throw new Exception('Please wait for the current render');
            } else {
                //queries
                $hash = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = " . $userid);
                $hash->execute();
                $hash = $hash->fetch(PDO::FETCH_OBJ);
                $headshothash = $hash->HeadshotThumbHash;
                $headshotAngelRight = $hash->headshotAngleRight;
                $headshotAngleLeft = $hash->headshotAngleLeft;
                $hash = $hash->ThumbHash;

                
                $wearingcolors = $GLOBALS['pdo']->prepare('SELECT * FROM body_colours WHERE uid = ' . $userid);
                $wearingcolors->execute();
                $wearingcolors = $wearingcolors->fetch(PDO::FETCH_OBJ);

                //users current body colors
                $head = (int)$wearingcolors->h;
                $torso = (int)$wearingcolors->t;
                $leftarm = (int)$wearingcolors->la;
                $rightarm = (int)$wearingcolors->ra;
                $leftleg = (int)$wearingcolors->ll;
                $rightleg = (int)$wearingcolors->rl;

                //currently wearing items
                $assets = User::GetWearingAssetsString($userid);

                //add to db
                $outfit = $GLOBALS['pdo']->prepare("INSERT INTO user_outfits(userid, assets, name, h, t, la, ra, ll, rl, headshotAngleRight, headshotAngleLeft, ThumbHash, HeadshotThumbHash, whenCreated) VALUES (:uid, :assets, :name, :h, :t, :la, :ra, :ll, :rl, :har, :hal, :th, :hth, UNIX_TIMESTAMP())");
                $outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
                $outfit->bindParam(":assets", $assets, PDO::PARAM_STR);
                $outfit->bindParam(":name", $name, PDO::PARAM_STR);
                $outfit->bindParam(":h", $head, PDO::PARAM_INT);
                $outfit->bindParam(":t", $torso, PDO::PARAM_INT);
                $outfit->bindParam(":la", $leftarm, PDO::PARAM_INT);
                $outfit->bindParam(":ra", $rightarm, PDO::PARAM_INT);
                $outfit->bindParam(":ll", $leftleg, PDO::PARAM_INT);
                $outfit->bindParam(":rl", $rightleg, PDO::PARAM_INT);
                $outfit->bindParam(":har", $headshotAngelRight, PDO::PARAM_INT);
                $outfit->bindParam(":hal", $headshotAngleLeft, PDO::PARAM_INT);
                $outfit->bindParam(":th", $hash, PDO::PARAM_STR);
                $outfit->bindParam(":hth", $headshothash, PDO::PARAM_STR);
                $outfit->execute();	
                return true;
            }
        }

        public static function DeleteOutfit(int $userid, int $outfitid)
        {
            if (!Outfit::UserOwnsOutfit($userid, $outfitid)) {
                throw new Exception('Error occurred');
            } else {
                $delete = $GLOBALS['pdo']->prepare("DELETE from user_outfits WHERE userid = :uid AND id = :id");
                $delete->bindParam(":uid", $userid, PDO::PARAM_INT);
                $delete->bindParam(":id", $outfitid, PDO::PARAM_INT);
                $delete->execute();
                if ($delete->rowCount() > 0) {
                    return true;
                }
            }
        }

        public static function UpdateOutfit(int $userid, int $outfitid, string $name)
        {
            $name = cleanInput($name);

            if (strlen($name) <= 3) {
                throw new Exception('Name too short, must be above 3 characters 1');
            } else if (strlen($name) >= 50) {
                throw new Exception('Name too long, must be less than 50 characters 1');
            } else if (!Outfit::UserOwnsOutfit($userid, $outfitid)) {
                throw new Exception('Error occurred');
            } else if (Render::PendingRender($userid)) {
                throw new Exception('Please wait for the current render');
            } else if (!Outfit::DeleteOutfit($userid, $outfitid)) {
                throw new Exception('Failed to update outfit, contact an Administrator');
            } else if (!Outfit::CreateOutfit($name, $userid)) {
                throw new Exception('Failed to update outfit, contact an Administrator');
            } else {
                return true;
            }
        }

        public static function ApplyOutfit(int $userid, int $outfitid)
        {
            if (!Outfit::UserOwnsOutfit($userid, $outfitid)) {
                throw new Exception('Error occurred');
            } else if (Render::PendingRender($userid)) {
                throw new Exception('Please wait for the current render');
            } else {
                $outfit = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE userid = :uid AND id = :id');
                $outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
                $outfit->bindParam(":id", $outfitid, PDO::PARAM_INT);
                $outfit->execute();
                if ($outfit->rowCount() == 0) {
                    throw new Exception('Error occurred');
                } else {
                    //vars
                    $outfit = $outfit->fetch(PDO::FETCH_OBJ);
                    $outfitassets = explode(";", $outfit->assets);

                    //outfit body colors
                    $outfithead = (int)$outfit->h;
                    $outfittorso = (int)$outfit->t;
                    $outfitleftarm = (int)$outfit->la;
                    $outfitrightarm = (int)$outfit->ra;
                    $outfitleftleg = (int)$outfit->ll;
                    $outfitrightleg = (int)$outfit->rl;

                    //headshot settings
                    $headshotAngelRight = $outfit->headshotAngleRight;
                    $headshotAngleLeft = $outfit->headshotAngleLeft;

                    //apply outfit body colors
                    $bodycolor = $GLOBALS['pdo']->prepare("UPDATE body_colours SET h = :h, t = :t, la = :la, ra = :ra, ll = :ll, rl = :rl WHERE uid = :uid");
                    $bodycolor->bindParam(":h", $outfithead, PDO::PARAM_INT);
                    $bodycolor->bindParam(":t", $outfittorso, PDO::PARAM_INT);
                    $bodycolor->bindParam(":la", $outfitleftarm, PDO::PARAM_INT);
                    $bodycolor->bindParam(":ra", $outfitrightarm, PDO::PARAM_INT);
                    $bodycolor->bindParam(":ll", $outfitleftleg, PDO::PARAM_INT);
                    $bodycolor->bindParam(":rl", $outfitrightleg, PDO::PARAM_INT);
                    $bodycolor->bindParam(":uid", $userid, PDO::PARAM_INT);
                    $bodycolor->execute();

                    //delete all wearing items
                    $deequip = $GLOBALS['pdo']->prepare("DELETE from wearing_items WHERE uid = :u"); //delete all wearing
                    $deequip->bindParam(":u", $userid, PDO::PARAM_INT);
                    $deequip->execute();

                    //apply items in the outfit
                    foreach($outfitassets as $asset)
                    {
                        if ($asset != "") //hack for outfits with no wearing items
                        {
                            $equip = $GLOBALS['pdo']->prepare("INSERT INTO wearing_items(uid,aid,whenWorn) VALUES(:u,:a,UNIX_TIMESTAMP())");
                            $equip->bindParam(":u", $userid, PDO::PARAM_INT);
                            $equip->bindParam(":a", $asset, PDO::PARAM_INT);
                            $equip->execute();
                        }
                    }

                    //delete current render and headshot if its not part of an outfit
                    $prevhash = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
                    $prevhash->bindParam(":i", $userid, PDO::PARAM_INT);
                    $prevhash->execute();
                    $prevhash = $prevhash->fetch(PDO::FETCH_OBJ);
                    $oldhash = $prevhash->ThumbHash;
                    $oldheadshothash = $prevhash->HeadshotThumbHash;
                    if (!Outfit::ThumbHashInOutfit($oldhash)) {
                        unlink($GLOBALS['renderCDNPath'] . $oldhash);
                    } else if (!Outfit::HeadshotThumbHashInOutfit($oldheadshothash)) {
                        unlink($GLOBALS['renderCDNPath'] . $oldheadshothash);
                    }
                    
                    //outfits hashes
                    $hash = $outfit->ThumbHash;
                    $headshothash = $outfit->HeadshotThumbHash;

                    if ($headshothash == NULL) //outfit was created before headshots release (probably?)
                    {
                        Render::RenderPlayerCloseup($userid);

                        $headshothash = userInfo($userid)->HeadshotThumbHash;

                        $update = $GLOBALS['pdo']->prepare('UPDATE user_outfits SET HeadshotThumbHash = :hhash WHERE id = :oid');
                        $update->bindParam(":hhash", $headshothash, PDO::PARAM_STR);
                        $update->bindParam(":oid", $outfitid, PDO::PARAM_INT);
                        $update->execute();
                    }

                    //apply the outfit (yay less render server load)
                    $user = $GLOBALS['pdo']->prepare('UPDATE users SET ThumbHash = :hash, HeadshotThumbHash = :hhash, headshotAngleRight = :har, headshotAngleLeft = :hal WHERE id = ' . $userid);
                    $user->bindParam(":hash", $hash, PDO::PARAM_STR);
                    $user->bindParam(":hhash", $headshothash, PDO::PARAM_STR);
                    $user->bindParam(":har", $headshotAngelRight, PDO::PARAM_INT);
                    $user->bindParam(":hal", $headshotAngleLeft, PDO::PARAM_INT);
                    $user->execute();

                    return true;
                }
            }
        }
    }
}