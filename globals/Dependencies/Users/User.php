<?php

namespace Alphaland\Users {

    use PDO;

    class User
    {
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
    }
}