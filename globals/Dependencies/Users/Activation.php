<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Users {

    use Alphaland\Common\HashingUtiltity;
    use PDO;

    class Activation
    {
        private static function GenerateActivationCode()
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash(32);
                
                $keycheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `alphaland_verification` WHERE `activationcode` = :ac");
                $keycheck->bindParam(":ac", $hash, PDO::PARAM_STR);
                $keycheck->execute();
            } while($keycheck->fetchColumn(0) != 0);
            return $hash;
        }

        public static function GetUserActivationCode(int $userid)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT `activationcode` FROM `alphaland_verification` WHERE `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() == 1) {
                return $query->fetch(PDO::FETCH_OBJ)->activationcode;
            }
            return null;
        }
        
        public static function isUserActivated(int $userid)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `alphaland_verification` WHERE `isactivated` = 1 AND `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->fetchColumn(0) > 0) {
                return true;
            }
            return false;
        } 

        public static function setupUserActivation(int $userid) //this should be ran when the user first signs up
        {
            if (!Activation::IsUserActivated($userid)) {
                $activationcode = Activation::GenerateActivationCode();

                $n = $GLOBALS['pdo']->prepare("INSERT INTO `alphaland_verification`(`activationcode`,`uid`) VALUES(:ac, :userid)");
                $n->bindParam(":ac", $activationcode, PDO::PARAM_STR);
                $n->bindParam(":userid", $userid, PDO::PARAM_INT);
                $n->execute();

                return true;
            }
            return false;
        }
    }
}