<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Users {

    use Alphaland\Common\HashingUtiltity;
    use PDO;

    class Activation
    {

        private static PDO $pdo = $GLOBALS['pdo'];

        private static function GenerateActivationCode(): string
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash(32);
                
                $query = Activation::$pdo->prepare("SELECT COUNT(*) FROM `alphaland_verification` WHERE `activationcode` = :ac");
                $query->bindParam(":ac", $hash, PDO::PARAM_STR);
                $query->execute();
            } while ($query->fetchColumn(0) != 0);

            return $hash;
        }

        public static function GetUserActivationCode(int $userid): string
        {
            $query = Activation::$pdo->prepare("SELECT `activationcode` FROM `alphaland_verification` WHERE `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() == 1) {
                return (string)$query->fetch(PDO::FETCH_OBJ)->activationcode;
            }
            return null;
        }
        
        public static function IsUserActivated(int $userid): bool
        {
            $query = Activation::$pdo->prepare("SELECT COUNT(*) FROM `alphaland_verification` WHERE `isactivated` = 1 AND `uid` = :uid");
            $query->bindParam(":uid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->fetchColumn(0) > 0) {
                return true;
            }
            return false;
        }  

        public static function SetupUserActivation(int $userid): bool //this should be ran when the user first signs up
        {
            if (!Activation::IsUserActivated($userid)) {
                $activationcode = Activation::GenerateActivationCode();

                $n = Activation::$pdo->prepare("INSERT INTO `alphaland_verification`(`activationcode`,`uid`) VALUES(:ac, :userid)");
                $n->bindParam(":ac", $activationcode, PDO::PARAM_STR);
                $n->bindParam(":userid", $userid, PDO::PARAM_INT);
                $n->execute();

                return true;
            }
            return false;
        }
    }
}