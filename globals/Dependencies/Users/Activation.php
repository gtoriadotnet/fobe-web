<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Users {

    use PDO;

    class Activation
    {
        private static function generateActivationCode()
        {
            $hash = "";
            while (true) {
                $hash = genHash(32);
                
                $keycheck = $GLOBALS['pdo']->prepare("SELECT * FROM `alphaland_verification` WHERE `activationcode` = :ac");
                $keycheck->bindParam(":ac", $hash, PDO::PARAM_STR);
                $keycheck->execute();
                if ($keycheck->rowCount() == 0) {
                    break;
                }
            }
            return $hash;
        }

        public static function getUserActivationCode(int $userid)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `alphaland_verification` WHERE `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() == 1) {
                return $query->fetch(PDO::FETCH_OBJ)->activationcode;
            }
            return false;
        }
        
        public static function isUserActivated(int $userid)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `alphaland_verification` WHERE `isactivated` = 1 AND `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                return true;
            }
            return false;
        } 

        public static function setupUserActivation(int $userid) //this should be ran when the user first signs up
        {
            if (!Activation::isUserActivated($userid)) {
                $activationcode = Activation::generateActivationCode();

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